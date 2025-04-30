<?php 
// View for notification settings
// Loaded within 'app' layout
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-bell me-2"></i>Notification Settings</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Configure which email notifications you want to receive. You will still receive all in-app notifications regardless of these settings.
                    </p>

                    <?php SessionHelper::displayFlashMessages(); ?>

                    <form action="<?= URLROOT ?>/notification/settings" method="POST">
                        <div class="form-group mb-4">
                            <label class="form-label fw-bold">Email Notifications</label>
                            <div class="list-group">
                                <div class="list-group-item">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="permit_renewal" name="permit_renewal" <?= $data['emailPreferences']['permit_renewal'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="permit_renewal">
                                            <strong>Permit Renewal Reminders</strong>
                                            <p class="text-muted small mb-0">Receive email notifications when your business permits are about to expire.</p>
                                        </label>
                                    </div>
                                </div>
                                <div class="list-group-item">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="payment_confirmation" name="payment_confirmation" <?= $data['emailPreferences']['payment_confirmation'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="payment_confirmation">
                                            <strong>Payment Confirmations</strong>
                                            <p class="text-muted small mb-0">Receive email notifications when your payments are confirmed or rejected.</p>
                                        </label>
                                    </div>
                                </div>
                                <div class="list-group-item">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="application_status" name="application_status" <?= $data['emailPreferences']['application_status'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="application_status">
                                            <strong>Application Status Updates</strong>
                                            <p class="text-muted small mb-0">Receive email notifications when your application status changes.</p>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= URLROOT ?>/notification" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Notifications
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>