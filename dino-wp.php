<?php
/*
Plugin Name: DINO WP
Plugin URI: http://www.dino.com.br
Description: Ferramenta para visualização de notícias distribuídas pelo DINO - Visibilidade Online.
Version: 1.0.4
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

    $the_page_title = "newsdino";
    $the_page_name = 'DINO';

    $cssLivre = "";
    $cssTitulo = "display:none;";
    $cssResumo = "color:#808080; margin-top:5px; display:inline;";
    $cssLocal = "color:#08c; font-weight:bold;";
    $cssData = "font-weight:bold;";
    $cssCorpo = "";
    $cssLink = "";
    $cssArquivos = "float:right; margin:3%; width:40%;";

    $widgetH = 550;
    $widgetW = 250;

    $optionsCss = array("Livre" => $cssLivre, "Titulo" => $cssTitulo, "Resumo" => $cssResumo, "Local" => $cssLocal, "Data" => $cssData, "Corpo" => $cssCorpo, "Link" => $cssLink, "Arquivos" => $cssArquivos);
    $options = array("Parceiro" => "", "Html" => "");
    $optionsWidget = array("Height" => $widgetH, "Width" => $widgetW);

    delete_option("dino_plugin_page_title");
    add_option("dino_plugin_page_title", $the_page_title, '', 'yes');

    delete_option("dino_plugin_page_name");
    add_option("dino_plugin_page_name", $the_page_name, '', 'yes');

    delete_option("dino_plugin_page_id");
    add_option("dino_plugin_page_id", '0', '', 'yes');

    delete_option("dino_plugin_option");
    add_option("dino_plugin_option", $options, '', 'yes');

    delete_option("dino_plugin_option_css");
    add_option("dino_plugin_option_css", $optionsCss, '', 'yes');

    delete_option("dino_plugin_widget");
    add_option("dino_plugin_widget", $optionsWidget, '', 'yes');

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
    delete_option("dino_plugin_option_css");
    delete_option("dino_plugin_widget");

}

function dino_plugin_page_filter( $posts ) {

global $wp_query;

global $_GET;

if(!is_null($wp_query))
{

if( $wp_query->get('dino_plugin_page_is_called') ) {

$releaseid = $_GET["releaseid"];

$url = "http://www.dino.com.br/api/news/".$releaseid;

$json = file_get_contents($url);

$release = json_decode($json);

$date = new DateTime($release->{'PublishedDate'});

$css = get_option('dino_plugin_option_css');
$opti = get_option('dino_plugin_option');
$html = $opti["Html"];

$cont = '<div>'.$html.'</div>';

if($release == NULL || $releaseid == NULL)
{
    $posts[0]->post_title = "Notícia não localizada";

    $cont .= '<div class="entry-content"><p>Notícia não encontrada, verifique o endereço digitado.</p></div>';

    $posts[0]->post_content = $cont;
}else
{
    $posts[0]->post_title = $release->{'Title'};

    $cont .= '<div class="dinotitulo"><h1 class="entry-title">'.$release->{'Title'}.'</h1></div>';
    $cont .= '<div><div id="dresumo"><span class="dinolocal">'.$release->{'Place'}.' </span><span class="dinodata">'.$date->format("d/m/Y").'</span> - <h2 class="dinoresumo ">'.$release->{'Summary'}.'</h2></div>';
    $cont .= '<div class="dinoarquivos">';
    if($release->{'ImageID'} != NULL)
    {
        $cont .= '<img itemprop="photo" src="'.$release->{'MainPictureUrl'}.'"/><br/>';
    }

    if($release->{'VideoUrl'} != NULL)
    {
        $cont .= '<br/><iframe src="'.$release->{'VideoUrl'}.'?rel=0" frameborder="0" allowfullscreen></iframe>';
    }

    $cont .= '</div><p class="dinocorpo entry-content"><br/>'.$release->{'Body'}.'</p>';
    $cont .= '<div class="dinolink"><a href="'.$release->{'SourceUrl'}.'">Leia mais</a></div></div>';
    $cont .= '<style>.dinotitulo{'.$css["Titulo"].'}.dinoresumo{'.$css["Resumo"].'}.dinolocal{'.$css["Local"].'}.dinodata{'.$css["Data"].'}.dinocorpo{'.$css["Corpo"].'}.dinolink{'.$css["Link"].'}.dinoarquivos{'.$css["Arquivos"].'}#dresumo br{display:none;}'.$css["Livre"].'</style>';
    


    $analytics =  '<script type="text/javascript" title="Analytics">';
           $analytics .= "var _gaq = _gaq || [];_gaq.push(['_setAccount', 'UA-28239442-1']);";
           $analytics .= "_gaq.push(['_setCustomVar', 1, 'partner', '".$opti["Parceiro"]."', 3]);_gaq.push(['_setCustomVar', 2, 'release', '".$releaseid."', 3]);_gaq.push(['_trackPageview']);";
           $analytics .= "(function () {var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);})();</script>";

    $ti = get_bloginfo();
    $script = '<script type="text/javascript" title="Titulo">var titulo = "'.$release->{'Title'}." | ".$ti.'"; if (document.title.search("newsdino") !== -1){ document.title = titulo; } </script>';

    $posts[0]->post_content = $cont.$analytics.$script;

    $hook = add_action("wp_head","page_meta");

if(!function_exists("page_meta"))
{
    function page_meta(){
        $releaseid2 = $_GET["releaseid"];
        $url2 = "http://www.dino.com.br/api/news/".$releaseid2;
        $json2 = file_get_contents($url2);
        $release2 = json_decode($json2);

        $summary = encurtador( $release2->{'Summary'}, 160 );
        $title = encurtador( $release2->{'Title'}, 170 );

        $metaContent = '<meta name="description" content="'."$summary".'" />';
        $metaContent .= '<meta property="og:title" content="'."$title".'" />';
        $metaContent .= '<meta  property="og:description" content="'."$summary".'" />';


        return print($metaContent);
    }
}
    do_action("$hook");
}
}

return $posts;

}
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

function dino_admin_menu() {
add_options_page('DINO - WP Plugin Settings', 'DINO - WP', 'administrator',__FILE__, 'dino_setting_page',plugins_url('/images/icon.png',_FILE_ ));
add_action( 'admin_init', 'register_dinosettings' );
}

function register_dinosettings() {
	register_setting( 'dino_settings_group', 'dino_plugin_option' );
    register_setting( 'dino_settings_group', 'dino_plugin_option_css' );
}

function encurtador($texto, $tamanho){
    $t = strip_tags($texto);
    if(strlen($texto) > $tamanho){
        return substr($t,0,$tamanho-3).'...';
    }
    return $t;
}

function dinopagelink(){
    global $wpdb;
    $pageid = get_option('dino_plugin_page_id');
    $actual_link = "http://$_SERVER[HTTP_HOST]?page_id=$pageid";
    return $actual_link;
}

class wctest{
    public function __construct(){
        if(is_admin()){
	    add_action('admin_menu', array($this, 'add_plugin_page'));

	    add_action('admin_init', array($this, 'page_init'));
        
        function pw_load_scripts() {
            wp_enqueue_style( 'bootstrap-css', plugins_url( 'dino-wp/css/bootstrap.min.css' , dirname(__FILE__) ) );
            
            wp_enqueue_script( 'bootstrap-js', plugins_url( 'dino-wp/js/bootstrap.min.js' , dirname(__FILE__) ) );
            wp_enqueue_script( 'jquery-1102-js', plugins_url( 'dino-wp/js/jquery-1.10.2.min.js' , dirname(__FILE__) ) );
        }

         add_action('admin_enqueue_scripts', 'pw_load_scripts');
       
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
	        <?php settings_fields('dino_option_group');?>
            <br/>

            <ul class="nav nav-tabs" id="dino-tabs">
                <li class="active"><a href="#dino-geral" data-toggle="tab">Geral</a></li>
                <li><a href="#dino-aparencia" data-toggle="tab">Aparência</a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="dino-geral"><?php do_settings_sections('dino-setting-admin');?></div>
                <div class="tab-pane" id="dino-aparencia"><?php do_settings_sections('dino-setting-admin-css');?></div>
            </div>
	        <?php submit_button(); ?>
	    </form>
	</div>
<?php
    }
	
    public function page_init(){		
	register_setting('dino_option_group', 'dino_plugin_option');
    register_setting('dino_option_group', 'dino_plugin_option_css');
		
    add_settings_section(
	    'sessao_info',
	    'Geral',
	    array($this, 'print_section_info'),
	    'dino-setting-admin'
	);
    
        add_settings_field(
	        'dino_plugin_option', 
	        '', 
	        array($this, 'create_options_field'), 
	        'dino-setting-admin',
	        'sessao_info'			
	    );
    
    add_settings_section(
	    'sessao_aparencia',
	    'Aparência',
	    array($this, 'print_section_aparencia'),
	    'dino-setting-admin-css'
	);	
    	
	add_settings_field(
	    'dino_plugin_option_css', 
	    'Classes: dinotitulo, dinoresumo, dinolocal, dinodata, dinocorpo, dinolink', 
	    array($this, 'create_css_field'), 
	    'dino-setting-admin-css',
	    'sessao_aparencia'			
	);
    	
    }
	
    public function print_section_aparencia(){
	print 'CSS:';
    }

    public function print_section_info(){
	print "Informações";
    }

    public function print_pagina_noticia(){
	print '<a href="'.dinopagelink().'">'.dinopagelink().'</a>';
    }
	
    public function create_css_field(){
        $op = get_option('dino_plugin_option_css');

    ?>
        <div>
            <h3>CSS Livre</h3>
            <textarea style="width:100%; width: 80%; height:100px;" name="dino_plugin_option_css[Livre]" id="dinocss1"><?php echo $op["Livre"] ?></textarea>
        </div>

        <div>
            <h3>Título</h3>
            <textarea style="width:100%; width: 80%; height:30px;" name="dino_plugin_option_css[Titulo]" id="dinocss2"><?php echo $op["Titulo"] ?></textarea>
        </div>

        <div>
            <h3>Resumo</h3>
            <textarea style="width:100%; width: 80%; height:30px;" name="dino_plugin_option_css[Resumo]" id="dinocss3"><?php echo $op["Resumo"]?></textarea>
        </div>

        <div>
            <h3>Local</h3>
            <textarea style="width:100%; width: 80%; height:30px;" name="dino_plugin_option_css[Local]" id="dinocss4"><?php echo $op["Local"]?></textarea>
        </div>

        <div>
            <h3>Data</h3>
            <textarea style="width:100%; width: 80%; height:30px;" name="dino_plugin_option_css[Data]" id="dinocss5"><?php echo $op["Data"]?></textarea>
        </div>

        <div>
            <h3>Corpo</h3>
            <textarea style="width:100%; width: 80%; height:30px;" name="dino_plugin_option_css[Corpo]" id="dinocss6"><?php echo $op["Corpo"]?></textarea>
        </div>

        <div>
            <h3>Link</h3>
            <textarea style="width:100%; width: 80%; height:30px;" name="dino_plugin_option_css[Link]" id="dinocss7"><?php echo $op["Link"]?></textarea>
        </div>

        <div>
            <h3>Arquivos (imagem, video)</h3>
            <textarea style="width:100%; width: 80%; height:30px;" name="dino_plugin_option_css[Arquivos]" id="dinocss8"><?php echo $op["Arquivos"]?></textarea>
        </div>
    <?php
    }

    public function create_options_field(){
        $op_info = get_option('dino_plugin_option');
        $pagelink = dinopagelink();
    ?>
        <div>
            <h3>Página da Notícia</h3>
            <a href="<?php echo $pagelink ?>"><?php echo $pagelink ?></a>
        </div>

        <div>
            <h3>Número de registro</h3>
            <input type="text" name="dino_plugin_option[Parceiro]" id="dinooption1" value="<?php echo $op_info["Parceiro"]?>" />
            
        </div>
        
        <div>
            <h3>Html</h3>
            <textarea style="min-width:300px; width: 80%; height:50px;" name="dino_plugin_option[Html]" id="dinooption2"><?php echo $op_info["Html"]?></textarea>
        </div>
    <?php
    }
}

////Widget
//get_option("dino_plugin_widget");
class wp_dino_widget extends WP_Widget {
    
	// constructor
	function wp_dino_widget() {
        $wOp = get_option("dino_plugin_widget");


        $widget_ops = array( 'classname' => 'dinoList ', 'description' => __('Lista dos ultimos relases distribuidos no DINO.', 'dinoList') );  

		parent::WP_Widget(false, $name = __('DINO Widget', 'wp_dino_widget'), $widget_ops);
	}

	// widget form creation
	function form($instance) {
     $wOp = get_option("dino_plugin_widget");  
      	
	// Check values
    if( $instance) { 
     $h = esc_attr($instance['height']); 
     $w = esc_attr($instance['width']);
} else { 
     $h = $wOp["Height"]; 
     $w = $wOp["Width"]; 
} 
		?>
		<p>
		    <label for="<?php echo $this->get_field_name( 'height' ); ?>"><?php _e( 'Altura:' ); ?></label> 
		    <input style="width: 50px;" id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" value="<?php echo esc_attr( $h ); ?>" />
		    <label> px</label>
        </p>

        <p>
		    <label for="<?php echo $this->get_field_name( 'width' ); ?>"><?php _e( 'Largura:' ); ?></label> 
		    <input style="width: 50px;" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo esc_attr( $w ); ?>" />
		    <label> px</label>
        </p>
		<?php 
	}

	// widget update
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
      // Fields
      $instance['height'] = strip_tags($new_instance['height']);
      $instance['width'] = strip_tags($new_instance['width']);
     return $instance;
	}

	// widget display
	function widget($args, $instance) {
        extract( $args );

        $options = get_option("dino_plugin_option");
        $pID = $options["Parceiro"];

        $h = $instance['height'];
        $w = $instance['width'];

		echo '<iframe id="dinoFrame2" border="0" style="min-height:80px" name="widget" height="'.$h.'px" src="http://www.dino.com.br/widget/index?partnerid='.$pID.'" width="'.$w.'px" overflow:="" "hidden"="" marginheight="0" marginwidth="0" frameborder="no" scrolling="no"></iframe>';
	}
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("wp_dino_widget");'));


?>
