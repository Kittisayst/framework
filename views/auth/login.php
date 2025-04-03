<div class="d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-sm col-md-3">
        <div class="card-body p-4">
            <h1 class="text-center mb-4 h3">Sign in</h1>
            <?php
            View::useFlash();
            ?>

            <form action="<?= View::url('/auth/login') ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?= session('csrf_token') ?>">
                <div class="mb-3">
                    <input type="email" class="form-control" name="email" placeholder="Email"
                        value="<?= View::useOld('email') ?>" required>
                </div>

                <div class="mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label text-muted" for="remember">Remember password</label>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary text-uppercase">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>