<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_subscribers = $wpdb->prefix . 'man_subscribers';

$subscribers = $wpdb->get_results("SELECT * FROM $table_subscribers ORDER BY created_at DESC");
?>

<div class="wrap man-admin-wrap">
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-6 fw-bold">Gestion des Abonnés</h1>
            <div>
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=man-subscribers&action=export_csv'), 'man_export_subscribers'); ?>" class="btn btn-outline-primary me-2">Exporter CSV</a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubscriberModal">Ajouter un abonné</button>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" class="form-check-input" id="selectAll"></th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Date d'inscription</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($subscribers) : foreach ($subscribers as $sub) : ?>
                                <tr>
                                    <td><input type="checkbox" class="form-check-input sub-checkbox" value="<?php echo $sub->id; ?>"></td>
                                    <td><?php echo esc_html($sub->email); ?></td>
                                    <td>
                                        <span class="badge <?php echo $sub->status === 'active' ? 'bg-success' : ($sub->status === 'pending' ? 'bg-warning' : 'bg-danger'); ?>">
                                            <?php echo ucfirst(esc_html($sub->status)); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($sub->created_at))); ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-danger delete-subscriber" data-id="<?php echo $sub->id; ?>">Supprimer</button>
                                    </td>
                                </tr>
                            <?php endforeach; else : ?>
                                <tr><td colspan="5" class="text-center">Aucun abonné trouvé.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Subscriber Modal -->
<div class="modal fade" id="addSubscriberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvel Abonné</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addSubscriberForm">
                    <div class="mb-3">
                        <label class="form-label">Adresse Email</label>
                        <input type="email" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Ajouter</button>
                </form>
            </div>
        </div>
    </div>
</div>
