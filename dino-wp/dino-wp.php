<?php
/*
Plugin Name: DINO WP
Plugin URI: http://www.dino.com.br
Description: Ferramenta para visualização de notícias distribuídas pelo DINO - Visibilidade Online.
Version: 1.0.2
Author: DINO
Author URI: http://www.dino.com.br
License: GPL2
*/

register_activation_hook(__FILE__,'dino_plugin_install'); 

register_deactivation_hook( __FILE__, 'dino_plugin_remove' );

add_filter( 'the_posts', 'dino_plugin_page_filter' );

add_filter( 'parse_query', 'dino_plugin_query_parser' );

if ( is_admin() ){

$wctest = new wctest();
}

//Functions

function dino_plugin_install() {

    global $wpdb;

    $the_page_title = "DINO - Divulgador e Visibilidade Online";
    $the_page_name = 'DINO';

    $options = '.dinoresumo{color:#808080;margin-top: 5px;text-align: left;} .dinodata{text-align: right;}';

    delete_option("dino_plugin_page_title");
    add_option("dino_plugin_page_title", $the_page_title, '', 'yes');

    delete_option("dino_plugin_page_name");
    add_option("dino_plugin_page_name", $the_page_name, '', 'yes');

    delete_option("dino_plugin_page_id");
    add_option("dino_plugin_page_id", '0', '', 'yes');

    delete_option("dino_plugin_option");
    add_option("dino_plugin_option", $options, '', 'yes');

    $the_page = get_page_by_title( $the_page_title);

    if ( ! $the_page ) {

        $_p = array();
        $_p['post_title'] = $the_page_title;
        $_p['post_content'] = "DINO - Divulgador e Visibilidade Online. NÃO DELETE.";
        $_p['post_status'] = 'private';//'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1); // the default 'Uncatrgorised'
        $_p['post_name'] = $page_name;

        $the_page_id = wp_insert_post( $_p );

    }
    else {

        $the_page_id = $the_page->ID;

        $the_page->post_status = 'private';//'publish';
        $the_page->comment_status = 'closed';
        $the_page->ping_status = 'closed';
        $the_page->post_type = 'page';
        $the_page->post_content = "DINO - Divulgador e Visibilidade Online";
        $the_page->post_name = $page_name;
        $the_page_id = wp_update_post( $the_page );

    }

    delete_option( 'dino_plugin_page_id' );
    add_option( 'dino_plugin_page_id', $the_page_id );
}

function dino_plugin_remove() {

    global $wpdb;

    $the_page_title = get_option( "dino_plugin_page_title" );
    $the_page_name = get_option( "dino_plugin_page_name" );
    $the_page_id = get_option( 'dino_plugin_page_id' );
    if( $the_page_id ) {

        wp_delete_post( $the_page_id ); // this will trash, not delete

    }

    delete_option("dino_plugin_page_title");
    delete_option("dino_plugin_page_name");
    delete_option("dino_plugin_page_id");
    delete_option("dino_plugin_option");

}

function dino_plugin_page_filter( $posts ) {

global $wp_query;

global $_GET;

if( $wp_query->get('dino_plugin_page_is_called') ) {

$releaseid = $_GET["releaseid"];

$url = "http://www.dino.com.br/api/news/".$releaseid;

$json = file_get_contents($url);

$release = json_decode($json);

$date = new DateTime($release->{'PublishedDate'});

if($release == NULL || $releaseid == NULL)
{
    $posts[0]->post_title = "Notícia não localizada";
    $posts[0]->post_content = '<div class="entry-content"><p>Parece que algum estagiário esqueceu de alimentar o DINO.</p></div>';
}else
{
    $posts[0]->post_title = $release->{'Title'};
    $posts[0]->post_content = '<style>'.dino_css().'</style><div><h2 class="dinoresumo">'.$release->{'Summary'}.'</h2><div class="dinodata"><p>'.$date->format("d/m/Y").'</p></div><div class="dinocorpo entry-content">'.$release->{'Body'}.'</div><div class="dinolink"><a href="'.$release->{'SourceUrl'}.'">Leia mais</a></div></div>';
}
}

return $posts;

}

function dino_plugin_query_parser( $q ) {

$pp = get_page_by_title(get_option( 'dino_plugin_page_title' ));
$the_page_name = $pp->post_name;

$the_page_id = get_option( 'dino_plugin_page_id' );
$qv = $q->query_vars;

if( !$q->did_permalink AND ( isset( $q->query_vars['page_id'] ) ) AND ( intval($q->query_vars['page_id']) == $the_page_id ) ) {

$q->set('dino_plugin_page_is_called', TRUE );
return $q;

}
elseif( isset( $q->query_vars['pagename'] ) AND ( ($q->query_vars['pagename'] == $the_page_name) OR ($_pos_found = strpos($q->query_vars['pagename'],$the_page_name.'/') === 0) ) ) {

$q->set('dino_plugin_page_is_called', TRUE );
return $q;

}else {

$q->set('dino_plugin_page_is_called', FALSE );
return $q;

}

}

function dino_css(){
        global $wpdb;
        return get_option('dino_plugin_option');
    }

function dino_admin_menu() {
add_options_page('DINO - WP Plugin Settings', 'DINO - WP', 'administrator',__FILE__, 'dino_setting_page',plugins_url('/images/icon.png',_FILE_ ));
add_action( 'admin_init', 'register_dinosettings' );
}

function register_dinosettings() {
	register_setting( 'dino_settings_group', 'dino_plugin_option' );
}

function dinopagelink(){
    global $wpdb;
    $pageid = get_option('dino_plugin_page_id');
    $actual_link = "http://$_SERVER[HTTP_HOST]?page_id=$pageid";
    return $actual_link;
}

?>

<?php
class wctest{
    public function __construct(){
        if(is_admin()){
	    add_action('admin_menu', array($this, 'add_plugin_page'));
	    add_action('admin_init', array($this, 'page_init'));
	}
    }
	
    public function add_plugin_page(){
	add_options_page('DINO - WP Plugin Settings', 'DINO - WP', 'manage_options', 'dino_setting_page', array($this, 'create_admin_page'));
    }

    public function create_admin_page(){
        ?>
	<div class="wrap">
	    <?php screen_icon(); ?>
	    <h2>DINO - WP Configurações</h2>			
	    <form method="post" action="options.php">
	        <?php
		    settings_fields('dino_option_group');	
		    do_settings_sections('dino-setting-admin');
		?>
	        <?php submit_button(); ?>
	    </form>
	</div>
<?php
    }
	
    public function page_init(){		
	register_setting('dino_option_group', 'dino_plugin_option');
		
            add_settings_section(
	    'sessao_info',
	    'Página da Notícia',
	    array($this, 'print_section_info'),
	    'dino-setting-admin'
	);	

        add_settings_section(
	    'sessao_aparencia',
	    'Aparência',
	    array($this, 'print_section_aparencia'),
	    'dino-setting-admin'
	);	
    	
	add_settings_field(
	    'dino_plugin_option', 
	    'Classes: dinoresumo, dinodata, dinocorpo, dinolink', 
	    array($this, 'create_css_field'), 
	    'dino-setting-admin',
	    'sessao_aparencia'			
	);		
    }
	
    public function print_section_aparencia(){
	print 'CSS:';
    }

    public function print_section_info(){
	print dinopagelink();
    }
	
    public function create_css_field(){
    ?>
        <textarea style="min-width:300px; width: 80%; height:300px;" name="dino_plugin_option" id="dinocss">
            <?php echo get_option('dino_plugin_option'); ?>
        </textarea>
    <?php
    }
}
?>
