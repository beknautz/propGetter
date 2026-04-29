<?php
class UserController
{
    public static function index(): void
    {
        Auth::requireRole('admin');
        $users = User::all();
        View::render('users/index', ['pageTitle' => 'Users', 'users' => $users]);
    }

    public static function create(): void
    {
        Auth::requireRole('admin');
        View::render('users/create', ['pageTitle' => 'Add User']);
    }

    public static function store(): void
    {
        Auth::requireRole('admin');
        Auth::requireCsrf();

        $v = Validator::make($_POST, [
            'name'     => 'required|max:120',
            'email'    => 'required|email|max:180',
            'password' => 'required|min:8',
            'role'     => 'required|in:admin,acquisitions,marketing,viewer',
        ]);

        if ($v->fails()) {
            View::render('users/create', [
                'pageTitle' => 'Add User',
                'errors'    => $v->errors(),
                'old'       => $_POST,
            ]);
            return;
        }

        if (User::findByEmail($_POST['email'])) {
            View::render('users/create', [
                'pageTitle' => 'Add User',
                'errors'    => ['email' => 'That email is already in use.'],
                'old'       => $_POST,
            ]);
            return;
        }

        User::create([
            'name'     => $_POST['name'],
            'email'    => $_POST['email'],
            'password' => $_POST['password'],
            'role'     => $_POST['role'],
            'active'   => isset($_POST['active']) ? 1 : 0,
        ]);

        View::redirect('/users', 'User created successfully.', 'success');
    }

    public static function edit(int $id): void
    {
        Auth::requireRole('admin');
        $user = User::findById($id);
        if (!$user) { http_response_code(404); include VIEW_PATH . '/errors/404.php'; return; }
        View::render('users/edit', ['pageTitle' => 'Edit User', 'user' => $user]);
    }

    public static function update(int $id): void
    {
        Auth::requireRole('admin');
        Auth::requireCsrf();

        $user = User::findById($id);
        if (!$user) { http_response_code(404); include VIEW_PATH . '/errors/404.php'; return; }

        $rules = [
            'name'  => 'required|max:120',
            'email' => 'required|email|max:180',
            'role'  => 'required|in:admin,acquisitions,marketing,viewer',
        ];
        if (!empty($_POST['password'])) {
            $rules['password'] = 'min:8';
        }

        $v = Validator::make($_POST, $rules);
        if ($v->fails()) {
            View::render('users/edit', [
                'pageTitle' => 'Edit User',
                'user'      => $user,
                'errors'    => $v->errors(),
            ]);
            return;
        }

        $existing = User::findByEmail($_POST['email']);
        if ($existing && (int)$existing['id'] !== $id) {
            View::render('users/edit', [
                'pageTitle' => 'Edit User',
                'user'      => $user,
                'errors'    => ['email' => 'That email is already in use.'],
            ]);
            return;
        }

        $data = [
            'name'   => $_POST['name'],
            'email'  => $_POST['email'],
            'role'   => $_POST['role'],
            'active' => isset($_POST['active']) ? 1 : 0,
        ];
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }

        // Prevent admin from removing their own admin role or deactivating themselves
        if ($id === Auth::id()) {
            $data['role']   = 'admin';
            $data['active'] = 1;
        }

        User::update($id, $data);
        View::redirect('/users', 'User updated successfully.', 'success');
    }

    public static function destroy(int $id): void
    {
        Auth::requireRole('admin');
        Auth::requireCsrf();

        if ($id === Auth::id()) {
            View::redirect('/users', 'You cannot delete your own account.', 'danger');
            return;
        }

        Database::query('DELETE FROM users WHERE id = ?', [$id]);
        View::redirect('/users', 'User deleted.', 'success');
    }
}
