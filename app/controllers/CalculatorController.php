<?php
/**
 * PropIntel CRM - Deal Calculator Controller
 *
 * Buy & Hold, BRRRR, Flip, Wholesale calculators.
 * Results saved per lead as scenarios.
 */

class CalculatorController
{
    /** GET /leads/:id/calculator */
    public static function show(int $id): void
    {
        $lead     = Lead::findById($id);
        if (!$lead) { http_response_code(404); exit; }

        $scenarios = Database::fetchAll(
            'SELECT * FROM deal_calculations WHERE lead_id = ? ORDER BY created_at DESC',
            [$id]
        );

        View::render('calculator/index', [
            'pageTitle' => 'Deal Calculator — ' . $lead['address'],
            'lead'      => $lead,
            'scenarios' => $scenarios,
        ]);
    }

    /** POST /leads/:id/calculator */
    public static function calculate(int $id): void
    {
        $lead = Lead::findById($id);
        if (!$lead) { View::json(['error' => 'Lead not found'], 404); }

        $type = $_POST['calc_type'] ?? 'buy_hold';
        $data = $_POST;

        $result = match ($type) {
            'brrrr'     => self::calcBrrrr($data),
            'flip'      => self::calcFlip($data),
            'wholesale' => self::calcWholesale($data),
            default     => self::calcBuyHold($data),
        };

        // Save scenario
        Database::query(
            'INSERT INTO deal_calculations
                (lead_id, user_id, calc_type, name, purchase_price, arv, repairs,
                 closing_costs, holding_costs, rent, vacancy_pct, mgmt_pct,
                 taxes, insurance, loan_amount, interest_rate, down_payment, loan_term_years,
                 monthly_payment, noi, cash_flow, cap_rate, dscr, coc_return,
                 flip_profit, mao, wholesale_fee, cash_needed)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $id, Auth::id(), $type, $_POST['scenario_name'] ?? null,
                $data['purchase_price']  ?? null,
                $data['arv']             ?? null,
                $data['repairs']         ?? null,
                $data['closing_costs']   ?? null,
                $data['holding_costs']   ?? null,
                $data['rent']            ?? null,
                $data['vacancy_pct']     ?? null,
                $data['mgmt_pct']        ?? null,
                $data['taxes']           ?? null,
                $data['insurance']       ?? null,
                $data['loan_amount']     ?? null,
                $data['interest_rate']   ?? null,
                $data['down_payment']    ?? null,
                $data['loan_term_years'] ?? null,
                $result['monthly_payment'] ?? null,
                $result['noi']             ?? null,
                $result['cash_flow']       ?? null,
                $result['cap_rate']        ?? null,
                $result['dscr']            ?? null,
                $result['coc_return']      ?? null,
                $result['flip_profit']     ?? null,
                $result['mao']             ?? null,
                $result['wholesale_fee']   ?? null,
                $result['cash_needed']     ?? null,
            ]
        );

        if (isset($_SERVER['HTTP_HX_REQUEST'])) {
            View::render('calculator/partials/result', [
                'result' => $result,
                'type'   => $type,
                'inputs' => $data,
            ], null);
            return;
        }

        View::json($result);
    }

    // ── Calculators ───────────────────────────────────────────────────────────

    private static function calcBuyHold(array $d): array
    {
        $purchase   = (float)($d['purchase_price']  ?? 0);
        $rent       = (float)($d['rent']            ?? 0);
        $vacancy    = (float)($d['vacancy_pct']     ?? 5)  / 100;
        $mgmt       = (float)($d['mgmt_pct']        ?? 8)  / 100;
        $taxes      = (float)($d['taxes']           ?? 0);
        $insurance  = (float)($d['insurance']       ?? 0);
        $repairs    = (float)($d['repairs']         ?? 0);
        $closing    = (float)($d['closing_costs']   ?? 0);
        $downPay    = (float)($d['down_payment']    ?? 0);
        $loanAmt    = (float)($d['loan_amount']     ?? ($purchase - $downPay));
        $rate       = (float)($d['interest_rate']   ?? 7) / 100 / 12;
        $term       = (int)  ($d['loan_term_years'] ?? 30) * 12;

        // Monthly mortgage payment
        $monthlyPI = ($rate > 0 && $term > 0)
            ? ($loanAmt * $rate * pow(1 + $rate, $term)) / (pow(1 + $rate, $term) - 1)
            : 0;

        $effectiveRent  = $rent * (1 - $vacancy);
        $monthlyExpenses= ($effectiveRent * $mgmt) + ($taxes / 12) + ($insurance / 12);
        $noi            = ($effectiveRent - ($taxes / 12) - ($insurance / 12) - ($effectiveRent * $mgmt)) * 12;
        $cashFlow       = $effectiveRent - $monthlyExpenses - $monthlyPI;
        $capRate        = $purchase > 0 ? ($noi / $purchase) * 100 : 0;
        $annualDebt     = $monthlyPI * 12;
        $dscr           = $annualDebt > 0 ? $noi / $annualDebt : 0;
        $cashNeeded     = $downPay + $repairs + $closing;
        $annualCashFlow = $cashFlow * 12;
        $coc            = $cashNeeded > 0 ? ($annualCashFlow / $cashNeeded) * 100 : 0;

        return [
            'type'            => 'buy_hold',
            'monthly_payment' => round($monthlyPI, 2),
            'effective_rent'  => round($effectiveRent, 2),
            'monthly_expenses'=> round($monthlyExpenses, 2),
            'noi'             => round($noi, 2),
            'cash_flow'       => round($cashFlow, 2),
            'annual_cash_flow'=> round($annualCashFlow, 2),
            'cap_rate'        => round($capRate, 2),
            'dscr'            => round($dscr, 2),
            'coc_return'      => round($coc, 2),
            'cash_needed'     => round($cashNeeded, 2),
        ];
    }

    private static function calcBrrrr(array $d): array
    {
        $arv        = (float)($d['arv']            ?? 0);
        $purchase   = (float)($d['purchase_price']  ?? 0);
        $repairs    = (float)($d['repairs']         ?? 0);
        $closing    = (float)($d['closing_costs']   ?? 0);
        $holding    = (float)($d['holding_costs']   ?? 0);
        $rent       = (float)($d['rent']            ?? 0);
        $refiLtv    = (float)($d['refi_ltv']       ?? 75) / 100;

        $allIn      = $purchase + $repairs + $closing + $holding;
        $refiLoan   = $arv * $refiLtv;
        $cashLeft   = max(0, $allIn - $refiLoan);

        // Run buy-hold on refinanced amount
        $holdResult = self::calcBuyHold(array_merge($d, [
            'purchase_price' => $arv,
            'loan_amount'    => $refiLoan,
            'down_payment'   => 0,
        ]));

        return array_merge($holdResult, [
            'type'          => 'brrrr',
            'all_in_cost'   => round($allIn, 2),
            'refi_loan'     => round($refiLoan, 2),
            'cash_left_in'  => round($cashLeft, 2),
            'equity_created'=> round($arv - $allIn, 2),
            'mao'           => round($arv * 0.70 - $repairs, 2),
        ]);
    }

    private static function calcFlip(array $d): array
    {
        $arv        = (float)($d['arv']            ?? 0);
        $purchase   = (float)($d['purchase_price']  ?? 0);
        $repairs    = (float)($d['repairs']         ?? 0);
        $closing    = (float)($d['closing_costs']   ?? 0);
        $holding    = (float)($d['holding_costs']   ?? 0);
        $sellCost   = $arv * 0.08;   // estimated selling costs (6% commission + 2% closing)

        $allIn      = $purchase + $repairs + $closing + $holding;
        $profit     = $arv - $allIn - $sellCost;
        $mao        = $arv * 0.70 - $repairs;
        $roi        = $allIn > 0 ? ($profit / $allIn) * 100 : 0;

        return [
            'type'            => 'flip',
            'all_in_cost'     => round($allIn, 2),
            'sell_costs'      => round($sellCost, 2),
            'flip_profit'     => round($profit, 2),
            'mao'             => round($mao, 2),
            'roi_pct'         => round($roi, 2),
            'cash_needed'     => round($allIn, 2),
        ];
    }

    private static function calcWholesale(array $d): array
    {
        $arv        = (float)($d['arv']            ?? 0);
        $repairs    = (float)($d['repairs']         ?? 0);
        $purchase   = (float)($d['purchase_price']  ?? 0);
        $assignFee  = (float)($d['assignment_fee']  ?? 10000);

        $mao            = $arv * 0.70 - $repairs;
        $yourMaxOffer   = $mao - $assignFee;
        $potentialFee   = $mao - $purchase;

        return [
            'type'              => 'wholesale',
            'arv'               => round($arv, 2),
            'mao'               => round($mao, 2),
            'your_max_offer'    => round($yourMaxOffer, 2),
            'wholesale_fee'     => round($potentialFee, 2),
            'assignment_fee'    => round($assignFee, 2),
            'cash_needed'       => round($purchase, 2),
        ];
    }
}
