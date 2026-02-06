<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wtm-dashboard">
    <!-- Header Section -->
    <div class="wtm-dashboard-header">
        <div class="wtm-header-content">
            <h1 class="wp-heading-inline"><?php _e('Overview', 'wp-team-manager'); ?></h1>
            <p class="wtm-header-subtitle"><?php _e('Welcome back! Here is what is happening with your teams.', 'wp-team-manager'); ?></p>
        </div>
        <div class="wtm-header-actions">
             <a href="<?php echo admin_url('post-new.php?post_type=team_manager'); ?>" class="button button-primary wtm-btn-primary">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php _e('Add New Member', 'wp-team-manager'); ?>
            </a>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="wtm-stats-grid">
        <div class="wtm-stat-card">
            <div class="wtm-stat-icon">
                <span class="dashicons dashicons-groups"></span>
            </div>
            <div class="wtm-stat-content">
                <h3><?php echo esc_html($team_count); ?></h3>
                <p><?php _e('Total Members', 'wp-team-manager'); ?></p>
            </div>
            <div class="wtm-stat-trend up">
                <span class="dashicons dashicons-arrow-up-alt2"></span>
                <span>Active</span>
            </div>
        </div>
        
        <div class="wtm-stat-card">
            <div class="wtm-stat-icon wtm-performance-icon">
                <span class="dashicons dashicons-performance"></span>
            </div>
            <div class="wtm-stat-content">
                <h3 id="wtm-load-time">--</h3>
                <p><?php _e('Avg Load Time', 'wp-team-manager'); ?></p>
            </div>
        </div>
        
        <div class="wtm-stat-card">
            <div class="wtm-stat-icon wtm-analytics-icon">
                <span class="dashicons dashicons-chart-area"></span>
            </div>
            <div class="wtm-stat-content">
                <h3 id="wtm-cache-rate">--</h3>
                <p><?php _e('Cache Hit Rate', 'wp-team-manager'); ?></p>
            </div>
        </div>
        
        <div class="wtm-stat-card">
            <div class="wtm-stat-icon wtm-image-icon">
                <span class="dashicons dashicons-format-image"></span>
            </div>
            <div class="wtm-stat-content">
                <h3 id="wtm-image-opt">--</h3>
                <p><?php _e('Images Optimized', 'wp-team-manager'); ?></p>
            </div>
        </div>
    </div>

    <div class="wtm-dashboard-columns">
        <!-- Main Column -->
        <div class="wtm-main-column">
            <!-- Recent Activity -->
            <div class="wtm-dashboard-section">
                <div class="wtm-section-header">
                    <h2><?php _e('Recent Team Members', 'wp-team-manager'); ?></h2>
                    <a href="<?php echo admin_url('edit.php?post_type=team_manager'); ?>" class="wtm-view-all"><?php _e('View All', 'wp-team-manager'); ?> &rarr;</a>
                </div>
                
                <div class="wtm-recent-teams">
                    <?php if (!empty($recent_teams)): ?>
                        <div class="wtm-team-list">
                            <?php foreach ($recent_teams as $team): 
                                $job_title = get_post_meta($team->ID, '_wtm_job_title', true);
                                $email = get_post_meta($team->ID, '_wtm_email', true);
                            ?>
                                <div class="wtm-team-row">
                                    <div class="wtm-team-avatar">
                                        <?php echo get_the_post_thumbnail($team->ID, [40, 40]) ?: '<div class="wtm-placeholder-avatar"><span>' . mb_substr($team->post_title, 0, 1) . '</span></div>'; ?>
                                    </div>
                                    <div class="wtm-team-details">
                                        <h4><?php echo esc_html($team->post_title); ?></h4>
                                        <span class="wtm-team-meta"><?php echo esc_html($job_title ?: __('No job title', 'wp-team-manager')); ?></span>
                                    </div>
                                    <div class="wtm-team-status">
                                        <span class="wtm-status-badge published"><?php _e('Published', 'wp-team-manager'); ?></span>
                                    </div>
                                    <div class="wtm-team-date">
                                        <?php echo get_the_date('M j, Y', $team->ID); ?>
                                    </div>
                                    <div class="wtm-team-actions">
                                        <a href="<?php echo get_edit_post_link($team->ID); ?>" class="button button-small button-clean">
                                            <span class="dashicons dashicons-edit"></span>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="wtm-empty-state">
                            <div class="wtm-empty-icon">👥</div>
                            <h3><?php _e('No team members yet', 'wp-team-manager'); ?></h3>
                            <p><?php _e('Create your first team member to get started building your team.', 'wp-team-manager'); ?></p>
                            <a href="<?php echo admin_url('post-new.php?post_type=team_manager'); ?>" class="button button-primary">
                                <?php _e('Add First Member', 'wp-team-manager'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="wtm-sidebar-column">
            <!-- Quick Actions -->
            <div class="wtm-dashboard-section">
                <h2><?php _e('Quick Actions', 'wp-team-manager'); ?></h2>
                <div class="wtm-quick-actions-list">
                    <button class="wtm-action-card" data-action="create_sample_team">
                        <span class="dashicons dashicons-plus-alt2"></span>
                        <div class="wtm-action-text">
                            <strong><?php _e('Create Sample Team', 'wp-team-manager'); ?></strong>
                            <span><?php _e('Generate dummy data', 'wp-team-manager'); ?></span>
                        </div>
                    </button>
                    
                    <button class="wtm-action-card" data-action="clear_cache">
                        <span class="dashicons dashicons-update"></span>
                        <div class="wtm-action-text">
                            <strong><?php _e('Clear Cache', 'wp-team-manager'); ?></strong>
                            <span><?php _e('Refresh team display', 'wp-team-manager'); ?></span>
                        </div>
                    </button>
                    
                    <button class="wtm-action-card" data-action="optimize_images">
                        <span class="dashicons dashicons-format-image"></span>
                        <div class="wtm-action-text">
                            <strong><?php _e('Optimize Images', 'wp-team-manager'); ?></strong>
                            <span><?php _e('Compress thumbnails', 'wp-team-manager'); ?></span>
                        </div>
                    </button>
                </div>
            </div>
            
            <!-- Performance Tips -->
            <div class="wtm-dashboard-section wtm-tips-section">
                <h2><?php _e('Performance Tips', 'wp-team-manager'); ?></h2>
                <div class="wtm-tips-list">
                    <div class="wtm-tip-item">
                        <span class="dashicons dashicons-yes"></span>
                        <p><?php _e('Use WebP format for faster loading', 'wp-team-manager'); ?></p>
                    </div>
                    <div class="wtm-tip-item">
                        <span class="dashicons dashicons-yes"></span>
                        <p><?php _e('Enable caching for team grids', 'wp-team-manager'); ?></p>
                    </div>
                    <div class="wtm-tip-item">
                        <span class="dashicons dashicons-yes"></span>
                        <p><?php _e('Limit initial display to 10 members', 'wp-team-manager'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>