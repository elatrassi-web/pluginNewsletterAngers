<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_subscribers = $wpdb->prefix . 'man_subscribers';
$table_newsletters = $wpdb->prefix . 'man_newsletters';
$table_stats = $wpdb->prefix . 'man_stats';

$total_subscribers = $wpdb->get_var("SELECT COUNT(*) FROM $table_subscribers WHERE status = 'active'");
$total_campaigns = $wpdb->get_var("SELECT COUNT(*) FROM $table_newsletters WHERE status = 'sent'");
$total_opens = $wpdb->get_var("SELECT COUNT(*) FROM $table_stats WHERE action = 'open'");
$total_clicks = $wpdb->get_var("SELECT COUNT(*) FROM $table_stats WHERE action = 'click'");
?>

<div class="wrap man-admin-wrap">
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col">
                <h1 class="display-5 fw-bold text-primary">Angers Newsletter Dashboard</h1>
                <p class="lead">Gérez vos abonnés et vos campagnes en toute simplicité.</p>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-uppercase mb-2 opacity-75">Abonnés Actifs</h6>
                        <h2 class="display-6 fw-bold mb-0"><?php echo esc_html($total_subscribers); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-uppercase mb-2 opacity-75">Campagnes Envoyées</h6>
                        <h2 class="display-6 fw-bold mb-0"><?php echo esc_html($total_campaigns); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-uppercase mb-2 opacity-75">Ouvertures Totales</h6>
                        <h2 class="display-6 fw-bold mb-0"><?php echo esc_html($total_opens); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 bg-warning text-dark">
                    <div class="card-body">
                        <h6 class="text-uppercase mb-2 opacity-75">Clics Totaux</h6>
                        <h2 class="display-6 fw-bold mb-0"><?php echo esc_html($total_clicks); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Dernières Campagnes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sujet</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $recent_campaigns = $wpdb->get_results("SELECT * FROM $table_newsletters ORDER BY created_at DESC LIMIT 5");
                                    if ($recent_campaigns) :
                                        foreach ($recent_campaigns as $campaign) : ?>
                                            <tr>
                                                <td><?php echo esc_html($campaign->subject); ?></td>
                                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($campaign->created_at))); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $campaign->status === 'sent' ? 'bg-success' : 'bg-secondary'; ?>">
                                                        <?php echo ucfirst(esc_html($campaign->status)); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-outline-primary">Editer</a>
                                                </td>
                                            </tr>
                                        <?php endforeach;
                                    else : ?>
                                        <tr><td colspan="4" class="text-center">Aucune campagne trouvée.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Actions Rapides</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="admin.php?page=man-campaigns&action=new" class="btn btn-primary">Créer une Campagne</a>
                            <a href="admin.php?page=man-subscribers" class="btn btn-outline-secondary">Gérer les Abonnés</a>
                            <a href="admin.php?page=man-settings" class="btn btn-outline-secondary">Paramètres</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
