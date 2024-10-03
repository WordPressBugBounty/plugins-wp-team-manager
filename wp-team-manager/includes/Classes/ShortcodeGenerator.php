<?php
namespace DWL\Wtm\Classes;
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://http://www.dynamicweblab.com/
 * @since      1.0.0
 *
 * @package    Wp_Team_Manager
 * @subpackage Wp_Team_Manager/admin
 */

/**
 * Team manager Shortcode generator class
 */
class ShortcodeGenerator{

  use \DWL\Wtm\Traits\Singleton;

  protected function init(){

    \add_action('admin_menu', [$this, 'register_team_manager_submenu_page'] );

  }

  /**
   * Register Team manager shortcode generator submenu page
   *
   * @since 1.0
   */
  public function register_team_manager_submenu_page() {

    $tm_pagemenu = add_submenu_page( 
      'edit.php?post_type=team_manager', 
      'Shortcode Generator', 
      'Shortcode Generator (deprecated)', 
      'manage_options', 
      'team-manager-shortcode-generator', 
      [$this, 'team_manager_submenu_page_callback'] 
    ); 

    \add_action( 'admin_enqueue_scripts', [$this, 'add_admin_script'] );

    \add_action( 'admin_notices', [$this, 'admin_notice'] );
  }

  public function admin_notice() {

    $screen = get_current_screen();
    if('team_manager_page_team-manager-shortcode-generator' == $screen->id):
    ?>
    <div class="notice notice-warning is-dismissible">
    <?php echo \sprintf( "<p>Shortcode Generator is deprecated please use <a href='%s'>Team Generator</a> instate.</p>", esc_url( admin_url( 'edit.php?post_type=dwl_team_generator' ) ) ); ?>
    </div>
    <?php
    endif;
    }
    

  /**
   * Enque js and css for admin 
   *
   * @since 1.0
   */
  public function add_admin_script( $hook ) {

    if ( 'team_manager_page_team-manager-shortcode-generator' != $hook ) {
        return;
    }
    //print_r($hook );
    \wp_enqueue_style( 'wp-color-picker' );

    \wp_enqueue_script( 'team-manager-admin' ); 
    \wp_enqueue_style( 'team-manager-admin' );

    \wp_enqueue_style([
      'wp-team-font-awesome-admin',
      'wp-team-style-admin',
        'wp-team-slick-admin',
        'wp-team-slick-theme-admin'
      ]);
      \wp_enqueue_script([
        'wp-team-slick-admin',
        'wp-team-script-admin'
       ]);

  } 

  /**
   * Display Team manager shortcode generator 
   *
   * @since 1.0
   */
  function team_manager_submenu_page_callback() {
    ?>
      <div class="wrap">
        <div id="icon-tools" class="icon32"></div>
          <h2><?php \esc_html_e('Shortcode Generator (deprecated)','wp-team-manager'); ?></h2>
          <div class="container-fluid">
            <div class="row">
              <div id="shortcode_options_wrapper" class="col-3 pt-3 pb-3 pr-1 pl-1 shadow-sm rounded bg-light-subtle">
              
              <form id="tm_short_code">
                <div class="row mb-3">
                  <label class="col-sm-7 col-form-label" for="cat"><?php esc_html_e('Select Team Group:','wp-team-manager'); ?></label>
                  <div class="col-sm-4">
                    <select name="category" class="form-select" id="category">      
                      <option value="0"><?php \esc_html_e( "All Group", "wp-team-manager" ); ?></option>
                        <?php 
                          $terms = get_terms( "team_groups" );

                          if(is_array($terms)){
                            $count = count( $terms );

                            if ( $count > 0 ){
                              foreach ( $terms as $term ) {
                                  printf( "<option value='%s'>%s</option>", esc_attr( $term->slug ), esc_html( $term->name ) );
                              }
                            }
                          }
                        ?> 
                    </select>
                  </div>              
                </div>

                <div class="row mb-3">
                  <label class="col-sm-7 col-form-label" for="orderby"><?php esc_html_e('Order By:','wp-team-manager'); ?></label>
                  <div class="col-sm-4">
                    <select id="orderby" class="form-select" name="orderby">
                      <option value="menu_order" selected><?php esc_html_e( "Menu Order", "wp-team-manager" ); ?></option>
                      <option value="title"><?php esc_html_e( "Name", "wp-team-manager" ); ?></option>
                      <option value="ID"><?php esc_html_e( "ID", "wp-team-manager" ); ?></option>
                      <option value="date"><?php esc_html_e( "Date", "wp-team-manager" ); ?></option>
                      <option value="modified"><?php esc_html_e( "Modified", "wp-team-manager" ); ?></option>
                      <option value="rand"><?php esc_html_e( "Random", "wp-team-manager" ); ?></option>
                    </select>
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-7 col-form-label" for="posts_per_page"><?php esc_html_e('Number of entries to display:','wp-team-manager'); ?> </label>
                  <div class="col-sm-4">
                    <input class="form-control" id="posts_per_page" name="posts_per_page" type="text" value="0">
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-7 col-form-label" for="post__in"><?php esc_html_e( "Show this ids only (Example: 1,2,3):", "wp-team-manager" ); ?> </label>
                  <div class="col-sm-4">
                    <input class="form-control" id="post__in" name="post__in" type="text" value="">
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-7 col-form-label" for="post__not_in"><?php esc_html_e( "Remove ids from list (Example: 1,5,7):", "wp-team-manager" ); ?> </label>
                  <div class="col-sm-4">
                    <input class="form-control" id="post__not_in" name="post__not_in" type="text" value="">
                  </div>
                </div>

                <div class="row mb-3 wtm-layout-wrapper">
                  <label class="col-sm-7 col-form-label" for="layout"><?php esc_html_e('Select Type:','wp-team-manager'); ?></label>
                  <div class="col-sm-4">
                    <select id="layout" class="form-select" name="layout">
                      <option value="grid" selected><?php esc_html_e( "Grid", "wp-team-manager" ); ?></option>
                      <option value="list"><?php esc_html_e( "List", "wp-team-manager" ); ?></option>
                      <option value="slider"><?php esc_html_e( "Slider", "wp-team-manager" ); ?></option>
                    </select>
                  </div>
                </div>

                <div class="row mb-3 wtm-layout-wrapper">
                  <div class="col-sm-7"><?php esc_html_e('Select column number:','wp-team-manager'); ?></div>
                </div>

                <div class="d-flex flex-row mb-3 wtm-layout-wrapper">
                  <div class="row mb-3" >
                      <label class="col-form-label" for="large_column"><?php esc_html_e('Desktop:','wp-team-manager'); ?></label>
                        <div class="col-sm-7">
                          <select id="large_column" class="form-select" name="large_column">
                            <option value="12"><?php esc_html_e( "1", "wp-team-manager" ); ?></option>
                            <option value="6"><?php esc_html_e( "2", "wp-team-manager" ); ?></option>
                            <option value="4"><?php esc_html_e( "3", "wp-team-manager" ); ?></option>
                            <option value="3" selected><?php esc_html_e( "4", "wp-team-manager" ); ?></option>
                          </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                      <label class="col-form-label" for="tablet_column"><?php esc_html_e('Tablet:','wp-team-manager'); ?></label>
                        <div class="col-sm-7">
                          <select id="tablet_column" class="form-select" name="tablet_column">
                            <option value="12"><?php esc_html_e( "1", "wp-team-manager" ); ?></option>
                            <option value="6" selected><?php esc_html_e( "2", "wp-team-manager" ); ?></option>
                            <option value="4"><?php esc_html_e( "3", "wp-team-manager" ); ?></option>
                            <option value="3"><?php esc_html_e( "4", "wp-team-manager" ); ?></option>
                          </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                      <label class="col-form-label" for="mobile_column"><?php esc_html_e('Mobile:','wp-team-manager'); ?></label>
                        <div class="col-sm-7">
                          <select id="mobile_column" class="form-select" name="mobile_column">
                            <option value="12" selected><?php esc_html_e( "1", "wp-team-manager" ); ?></option>
                            <option value="6"><?php esc_html_e( "2", "wp-team-manager" ); ?></option>
                            <option value="4"><?php esc_html_e( "3", "wp-team-manager" ); ?></option>
                            <option value="3"><?php esc_html_e( "4", "wp-team-manager" ); ?></option>
                          </select>
                        </div>
                    </div>
                  </div>

                <div class="row mb-3">
                  <label class="col-sm-7 col-form-label" for="show_other_info"><?php esc_html_e('Show Other Info ?','wp-team-manager'); ?></label>
                  <div class="col-sm-4">
                    <select id="show_other_info" class="form-select" name="show_other_info">
                      <option value="yes" selected><?php esc_html_e( "Yes", "wp-team-manager" ); ?></option>
                      <option value="no"><?php esc_html_e( "No", "wp-team-manager" ); ?></option>
                    </select>
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-7 col-form-label" for="show_read_more"><?php esc_html_e('Show Read More ?','wp-team-manager'); ?></label>
                  <div class="col-sm-4">
                    <select id="show_read_more" class="form-select" name="show_read_more">
                      <option value="yes" selected><?php esc_html_e( "Yes", "wp-team-manager" ); ?></option>
                      <option value="no"><?php esc_html_e( "No", "wp-team-manager" ); ?></option>
                    </select>
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-7 col-form-label" for="show_social"><?php esc_html_e('Show Social ?','wp-team-manager'); ?></label>
                  <div class="col-sm-4">
                    <select id="show_social" class="form-select" name="show_social">
                      <option value="yes" selected><?php esc_html_e( "Yes", "wp-team-manager" ); ?></option>
                      <option value="no"><?php esc_html_e( "No", "wp-team-manager" ); ?></option>
                    </select>
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-7 col-form-label" for="tm_image_size"><?php esc_html_e('Select image size:','wp-team-manager'); ?></label>
                  <?php 
                    global $_wp_additional_image_sizes; 
                  ?>
                  <div class="col-sm-4">
                    <select id="image_size" class="form-select" name="image_size">
                      <option value="thumbnail" selected><?php echo  esc_html__( "thumbnail", "wp-team-manager" ); ?></option>
                      <?php foreach ( $_wp_additional_image_sizes as $size_name => $size_attrs ): ?>
                        <option value="<?php echo esc_attr( $size_name ); ?>"><?php echo esc_html( $size_name ) ; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>  
                
                <div class="row mb-3">
                  <label class="col-sm-7 col-form-label" for="image_style"><?php esc_html_e('Image style','wp-team-manager'); ?></label>
                  <div class="col-sm-4">
                    <select id="image_style" class="form-select" name="image_style">
                      <option value="rounded"><?php esc_html_e( "Rounded", "wp-team-manager" ); ?></option>
                      <option value="circle"><?php esc_html_e( "Circle", "wp-team-manager" ); ?></option>
                      <option value="boxed" selected><?php esc_html_e( "Boxed", "wp-team-manager" ); ?></option>
                    </select>
                  </div>
                </div>

                <div class="d-flex flex-row mb-3" >
                  <div class="row mb-3" >
                      <label class="col-form-label" for="bg_color"><?php esc_html_e('Card Background Color','wp-team-manager'); ?></label>
                      <div class="col-sm-7">
                        <input class="form-control wtm-color-picker" id="bg_color" name="bg_color" type="text" value="">
                      </div>
                  </div>
                  <div class="row mb-3">
                    <label class="col-form-label" for="social_color"><?php esc_html_e('Social Icon Color','wp-team-manager'); ?></label>
                    <div class="col-sm-7">
                      <input class="form-control wtm-color-picker" id="social_color" name="social_color" type="text" value="">
                    </div>
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-sm-12">
                    <p class="fs-5"><?php esc_html_e('Short Code','wp-team-manager'); ?></p>
                    <div id="shortcode_output_box" class="alert alert-dark"></div>
                  </div>
                </div>
              </form> 
            </div>
            <div id="wtpm_short_code_preview" class="col-9"><?php echo  esc_html__( "Preview Loading...", "wp-team-manager" ); ?></div>
          </div>
        </div>
      </div>
      <?php 
    }

}