<?php require APPROOT . '/views/layouts/includes/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Edit User</h1>
        <div>
            <a href="<?php echo URLROOT; ?>/admin/users" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
            <a href="<?php echo URLROOT; ?>/admin/view-user/<?php echo $data['user']->id; ?>" class="btn btn-outline-primary">
                <i class="fas fa-eye"></i> View User
            </a>
        </div>
    </div>

    <?php 
    // Display any flash messages
    if (isset($_SESSION['flash_messages']['user_message'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo $_SESSION['flash_messages']['user_message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['flash_messages']['user_message']);
    }
    ?>
    
    <form action="<?php echo URLROOT; ?>/admin/edit-user/<?php echo $data['user']->id; ?>" method="POST" class="needs-validation" novalidate>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Account Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control <?php echo (!empty($data['username_err'])) ? 'is-invalid' : ''; ?>" 
                               id="username" name="username" value="<?php echo $data['user']->username; ?>" required>
                        <div class="invalid-feedback">
                            <?php echo $data['username_err']; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" 
                               id="email" name="email" value="<?php echo $data['user']->email; ?>" required>
                        <div class="invalid-feedback">
                            <?php echo $data['email_err']; ?>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select <?php echo (!empty($data['role_err'])) ? 'is-invalid' : ''; ?>" 
                                id="role" name="role" required>
                            <option value="">Select role</option>
                            <option value="admin" <?php echo ($data['user']->role === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                            <option value="treasurer" <?php echo ($data['user']->role === 'treasurer') ? 'selected' : ''; ?>>Treasurer</option>
                            <option value="owner" <?php echo ($data['user']->role === 'owner') ? 'selected' : ''; ?>>Business Owner</option>
                        </select>
                        <div class="invalid-feedback">
                            <?php echo $data['role_err']; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Account Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="1" <?php echo ($data['user']->status == 1) ? 'selected' : ''; ?>>Active</option>
                            <option value="0" <?php echo ($data['user']->status == 0) ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="reset_password" name="reset_password" value="1">
                    <label class="form-check-label" for="reset_password">
                        Reset User Password
                    </label>
                    <div class="form-text">If checked, the user's password will be reset to a new random password and sent to their email address.</div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Personal Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control <?php echo (!empty($data['first_name_err'])) ? 'is-invalid' : ''; ?>" 
                               id="first_name" name="first_name" value="<?php echo $data['user']->first_name; ?>" required>
                        <div class="invalid-feedback">
                            <?php echo $data['first_name_err']; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control <?php echo (!empty($data['last_name_err'])) ? 'is-invalid' : ''; ?>" 
                               id="last_name" name="last_name" value="<?php echo $data['user']->last_name; ?>" required>
                        <div class="invalid-feedback">
                            <?php echo $data['last_name_err']; ?>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control <?php echo (!empty($data['phone_number_err'])) ? 'is-invalid' : ''; ?>" 
                           id="phone_number" name="phone_number" value="<?php echo $data['user']->phone_number; ?>">
                    <div class="invalid-feedback">
                        <?php echo $data['phone_number_err']; ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control <?php echo (!empty($data['address_err'])) ? 'is-invalid' : ''; ?>" 
                              id="address" name="address" rows="3"><?php echo $data['user']->address; ?></textarea>
                    <div class="invalid-feedback">
                        <?php echo $data['address_err']; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?php echo URLROOT; ?>/admin/view-user/<?php echo $data['user']->id; ?>" class="btn btn-outline-secondary me-md-2">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </form>
</div>

<?php require APPROOT . '/views/layouts/includes/footer.php'; ?>