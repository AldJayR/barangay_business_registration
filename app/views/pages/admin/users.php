<?php require APPROOT . '/views/layouts/includes/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">User Management</h1>
        <a href="<?php echo URLROOT; ?>/auth/register-staff" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Register New Staff
        </a>
    </div>

    <!-- User Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Administrators</h5>
                    <h2 class="card-text"><?php echo $data['userCounts']['admin'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Treasurers</h5>
                    <h2 class="card-text"><?php echo $data['userCounts']['treasurer'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Business Owners</h5>
                    <h2 class="card-text"><?php echo $data['userCounts']['owner'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Filters</h5>
        </div>
        <div class="card-body">
            <form action="<?php echo URLROOT; ?>/admin/users" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="admin" <?php echo $data['filters']['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="treasurer" <?php echo $data['filters']['role'] === 'treasurer' ? 'selected' : ''; ?>>Treasurer</option>
                        <option value="owner" <?php echo $data['filters']['role'] === 'owner' ? 'selected' : ''; ?>>Business Owner</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="1" <?php echo $data['filters']['status'] === 1 ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $data['filters']['status'] === 0 ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search username, name or email" 
                               value="<?php echo $data['filters']['search'] ?? ''; ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">User Accounts</h5>
        </div>
        <div class="card-body">
            <?php if (empty($data['users'])) : ?>
                <div class="alert alert-info">
                    No users found. Try clearing filters or search criteria.
                </div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['users'] as $user) : ?>
                                <tr>
                                    <td><?php echo $user->id; ?></td>
                                    <td><?php echo $user->username; ?></td>
                                    <td>
                                        <?php 
                                            echo $user->first_name . ' ' . $user->last_name;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            switch ($user->role) {
                                                case 'admin':
                                                    echo '<span class="badge bg-primary">Admin</span>';
                                                    break;
                                                case 'treasurer':
                                                    echo '<span class="badge bg-success">Treasurer</span>';
                                                    break;
                                                case 'owner':
                                                    echo '<span class="badge bg-info">Business Owner</span>';
                                                    break;
                                                default:
                                                    echo '<span class="badge bg-secondary">Unknown</span>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($user->status == 1) : ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else : ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user->created_at)); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo URLROOT; ?>/admin/view-user/<?php echo $user->id; ?>" 
                                               class="btn btn-sm btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo URLROOT; ?>/admin/edit-user/<?php echo $user->id; ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($data['pagination']['totalPages'] > 1) : ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-4">
                            <?php if ($data['pagination']['currentPage'] > 1) : ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo URLROOT; ?>/admin/users/<?php echo $data['pagination']['currentPage'] - 1; ?>?role=<?php echo $data['filters']['role']; ?>&status=<?php echo $data['filters']['status']; ?>&search=<?php echo $data['filters']['search']; ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $data['pagination']['currentPage'] - 2); $i <= min($data['pagination']['totalPages'], $data['pagination']['currentPage'] + 2); $i++) : ?>
                                <li class="page-item <?php echo $i == $data['pagination']['currentPage'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo URLROOT; ?>/admin/users/<?php echo $i; ?>?role=<?php echo $data['filters']['role']; ?>&status=<?php echo $data['filters']['status']; ?>&search=<?php echo $data['filters']['search']; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($data['pagination']['currentPage'] < $data['pagination']['totalPages']) : ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo URLROOT; ?>/admin/users/<?php echo $data['pagination']['currentPage'] + 1; ?>?role=<?php echo $data['filters']['role']; ?>&status=<?php echo $data['filters']['status']; ?>&search=<?php echo $data['filters']['search']; ?>">
                                        Next
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/layouts/includes/footer.php'; ?>