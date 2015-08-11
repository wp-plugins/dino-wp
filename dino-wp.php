<?php
/*
Plugin Name: DINO WP
Plugin URI: http://www.dino.com.br
Description: Ferramenta para visualização de notícias distribuídas pelo DINO - Visibilidade Online.
Version: 1.0.14
Author: DINO
Author URI: http://www.dino.com.br
License: GPL2
*/

function _isCurl()
{
    return function_exists('curl_version');
}

function dino_file_get_contents( $site_url )
{
    if (_isCurl()) {
        try
        {
            $ch = curl_init();
            $timeout = 10;
            curl_setopt($ch, CURLOPT_URL, $site_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json'));
            curl_setopt($ch, CURLOPT_USERAGENT, 'DinoNews');
            $file_contents = curl_exec($ch);

            if ($file_contents === false) {
                echo "cURL Error: " . curl_error($ch);
            }
            
            curl_close($ch);
            return $file_contents;
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
        }
    } else {
        try
        {
            return file_get_contents($site_url);
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
        }        
    }
    return null;
}

register_activation_hook(__FILE__, 'dino_plugin_install'); 

register_deactivation_hook(__FILE__, 'dino_plugin_remove');

add_filter('the_posts', 'dino_plugin_page_filter');

add_filter('parse_query', 'dino_plugin_query_parser');

if (is_admin()) {
    $wctest = new wctest();
}

//Functions

function dino_plugin_install() {
    global $wpdb;

    $the_page_title = "newsdino";
    $the_page_name = 'DINO';

    $the_pageList_title = "newsdinolist";
    $the_pageList_name = "DINOLIST";

    $cssLivre = "";
    $cssTitulo = "display:none;";
    $cssResumo = "color:#808080; margin-top:5px; display:inline;";
    $cssLocal = "color:#08c; font-weight:bold;";
    $cssData = "font-weight:bold;";
    $cssCorpo = "";
    $cssLink = "";
    $cssArquivos = "float:right; margin:3%; width:40%;";

    $mostrarLink = "no";

    $widgetH = 550;
    $widgetW = 250;

    $listH = 900;
    $listW = 670;

    $optionsCss = array("Livre" => $cssLivre, "Titulo" => $cssTitulo, "Resumo" => $cssResumo, "Local" => $cssLocal, "Data" => $cssData, "Corpo" => $cssCorpo, "Link" => $cssLink, "MostrarLink" => $mostrarLink, "Arquivos" => $cssArquivos);
    $options = array("Parceiro" => "", "Html" => "");
    $optionsWidget = array("Height" => $widgetH, "Width" => $widgetW);
    $optionsList = array("Height" => $listH, "Width" => $listW);

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

    //**************************************************************Lista
    
    delete_option("dino_plugin_pageList_title");
    add_option("dino_plugin_pageList_title", $the_pageList_title, '', 'yes');

    delete_option("dino_plugin_pageList_name");
    add_option("dino_plugin_pageList_name", $the_pageList_name, '', 'yes');

    delete_option("dino_plugin_pageList_id");
    add_option("dino_plugin_pageList_id", '0', '', 'yes');

    delete_option("dino_plugin_list");
    add_option("dino_plugin_list", $optionsList, '', 'yes');

    //*****************************************************

    $the_page = get_page_by_title($the_page_title);

    if (!$the_page ) {

        $_p = array();
        $_p['post_title'] = $the_page_title;
        $_p['post_content'] = "DINO - Divulgador e Visibilidade Online. NÃO DELETE.";
        $_p['post_status'] = 'private';//'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1); // the default 'Uncatrgorised'
        $_p['post_name'] = $page_name;

        $the_page_id = wp_insert_post($_p);
    } else {

        $the_page_id = $the_page->ID;

        $the_page->post_status = 'private';//'publish';
        $the_page->comment_status = 'closed';
        $the_page->ping_status = 'closed';
        $the_page->post_type = 'page';
        $the_page->post_content = "DINO - Divulgador e Visibilidade Online";
        $the_page->post_name = $page_name;
        $the_page_id = wp_update_post( $the_page );

    }

    delete_option('dino_plugin_page_id');
    add_option('dino_plugin_page_id', $the_page_id);

    //**********************************************************************Lista

    $the_pageList = get_page_by_title($the_pageList_title);

    if (!$the_pageList) {
        $_pl = array();
        $_pl['post_title'] = $the_pageList_title;
        $_pl['post_content'] = "DINO - Divulgador e Visibilidade Online. Lista* - NÃO DELETE.";
        $_pl['post_status'] = 'private';//'publish';
        $_pl['post_type'] = 'page';
        $_pl['comment_status'] = 'closed';
        $_pl['ping_status'] = 'closed';
        $_pl['post_category'] = array(1); // the default 'Uncatrgorised'
        $_pl['post_name'] = $pageList_name;

        $the_pageList_id = wp_insert_post($_pl);
    } else {
        $the_pageList_id = $the_page->ID;

        $the_pageList->post_status = 'private';//'publish';
        $the_pageList->comment_status = 'closed';
        $the_pageList->ping_status = 'closed';
        $the_pageList->post_type = 'page';
        $the_pageList->post_content = "DINO - Divulgador e Visibilidade Online. Lista* - NÃO DELETE.";
        $the_pageList->post_name = $pageList_name;
        $the_pageList_id = wp_update_post($the_pageList);
    }

    delete_option('dino_plugin_pageList_id');
    add_option('dino_plugin_pageList_id', $the_pageList_id);
}

function dino_plugin_remove() {
    global $wpdb;

    $the_page_title = get_option("dino_plugin_page_title");
    $the_page_name = get_option("dino_plugin_page_name");
    $the_page_id = get_option('dino_plugin_page_id');
    if ($the_page_id) {
        wp_delete_post($the_page_id); // this will trash, not delete
    }

    //*******************************************************************Lista
    
    $the_pageList_title = get_option("dino_plugin_pageList_title");
    $the_pageList_name = get_option("dino_plugin_pageList_name");
    $the_pageList_id = get_option('dino_plugin_pageList_id');
    if ($the_pageList_id) {
        wp_delete_post($the_pageList_id); // this will trash, not delete
    }

    delete_option("dino_plugin_page_title");
    delete_option("dino_plugin_page_name");
    delete_option("dino_plugin_page_id");

    delete_option("dino_plugin_pageList_title");
    delete_option("dino_plugin_pageList_name");
    delete_option("dino_plugin_pageList_id");

    delete_option("dino_plugin_option");
    delete_option("dino_plugin_option_css");
    delete_option("dino_plugin_widget");
    delete_option("dino_plugin_list");
}

function dino_plugin_page_filter($posts) {
    global $wp_query;
    global $_GET;

    $posts[0] = new stdClass();
    
    if (!is_null($wp_query)) {
        if ($wp_query->get('dino_plugin_page_is_called')) {
            $releaseid = $_GET["releaseid"];
            $url = "http://www.dino.com.br/api/news/".$releaseid;
            $json = dino_file_get_contents($url);
            $release = json_decode($json);
            $date = new DateTime($release->{'PublishedDate'});
            $css = get_option('dino_plugin_option_css');
            $opti = get_option('dino_plugin_option');
            $html = $opti["Html"];
            $cont = '<div>'.$html.'</div>';
            
            if ($release->{'Title'} == null || $releaseid == null) {
                $posts[0]->post_title = "Notícia não localizada";
                
                $cont .= '<div class="entry-content"><p>Notícia não encontrada, verifique o endereço digitado.</p></div>';
                $posts[0]->post_content = $cont;
            } else {
                $posts[0]->post_title = $release->{'Title'};
                
                $cont .= '<div class="dinotitulo"><h1 class="entry-title">'.$release->{'Title'}.'</h1></div>';
                $cont .= '<div><div><h2 class="dinoresumo "><span class="dinolocal">'.$release->{'Place'}.' </span><span class="dinodata">'.$date->format("d/m/Y").'</span> - '.$release->{'Summary'}.'</h2></div>';
                $cont .= '<div class="dinoarquivos">';
                
                if ($release->{'MainPictureUrl'} != null) {
                    $imagem = substr($release->{'MainPictureUrl'}, 0, strpos($release->{'MainPictureUrl'}, "?"));
                    $cont .= '<img itemprop="photo" src="'.$imagem.'"/><br/>';
                }
                
                if ($release->{'VideoUrl'} != null) {
                    $cont .= '<br/><iframe src="'.$release->{'VideoUrl'}.'?rel=0" frameborder="0" allowfullscreen></iframe>';
                }
                
                $cont .= '</div><p class="dinocorpo entry-content"><br/>'.$release->{'Body'}.'</p>';

                if ($css["MostrarLink"] == "on") {
                    $cont .= '<div class="dinolink"><a href="'.$release->{'SourceUrl'}.'">Leia mais</a></div>';
                }
                
                $cont .= '</div><style>.dinotitulo{'.$css["Titulo"].'}.dinolocal{'.$css["Local"].'}.dinodata{'.$css["Data"].'}.dinoresumo{'.$css["Resumo"].'}.dinocorpo{'.$css["Corpo"].'}.dinolink{'.$css["Link"].'}.dinoarquivos{'.$css["Arquivos"].'}'.$css["Livre"].'</style>';
                
                $analytics =  '<script type="text/javascript" title="Analytics">';
                $analytics .= "var _gaq = _gaq || [];_gaq.push(['_setAccount', 'UA-28239442-1']);";
                $analytics .= "_gaq.push(['_setCustomVar', 1, 'partner', '".$opti["Parceiro"]."', 3]);_gaq.push(['_setCustomVar', 2, 'release', '".$releaseid."', 3]);_gaq.push(['_trackPageview']);";
                $analytics .= "(function () {var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);})();</script>";
                
                $ti = get_bloginfo();
                $script = '<script type="text/javascript" title="Titulo">var titulo = "'.$release->{'Title'}." | ".$ti.'"; if (document.title.search("newsdino") !== -1){ document.title = titulo; } </script>';
                $posts[0]->post_content = $cont.$analytics.$script;
                $hook = add_action("wp_head", "page_meta");
                
                if (!function_exists("page_meta")) {
                    function page_meta() {
                        $releaseid2 = $_GET["releaseid"];
                        $url2 = "http://www.dino.com.br/api/news/".$releaseid2;
                        $json2 = dino_file_get_contents($url2);
                        $release2 = json_decode($json2);
                        
                        $summary = encurtador($release2->{'Summary'}, 160);
                        $title = encurtador($release2->{'Title'}, 170);
                        
                        $metaContent = '<meta name="description" content="'."$summary".'" />';
                        $metaContent .= '<meta property="og:title" content="'."$title".'" />';
                        $metaContent .= '<meta  property="og:description" content="'."$summary".'" />';
                        
                        if ($release2->{'MainPictureUrl'} != null) {
                            $image = substr($release2->{'MainPictureUrl'}, 0, strpos($release2->{'MainPictureUrl'}, "?"))."?quality=60&width=300&height=300";
                            $metaContent .= '<meta  property="og:image" content="'."$image".'" />';
                        }
                        
                        return print($metaContent);
                    }
                }
                
                do_action("$hook");
            }
        }
        
        if ($wp_query->get('dino_plugin_list_is_called')) {
            $list_options = get_option('dino_plugin_list');
            $dino_options = get_option('dino_plugin_option');
            $list_h = $list_options["Height"];
            $list_w = $list_options["Width"];
            $parceiro_id = $dino_options["Parceiro"];
            
            if ($list_h == null || $list_h == 0) {
                $list_h = 900;
            }
            
            if ($list_w == null || $list_w == 0) {
                $list_w = 670;
            }
            
            $posts[0]->post_title = "Releases DINO";
            
            $posts[0]->post_content = "<div style='width:".$list_w."px;'><script type='text/javascript'>var _dinopartId = new Array();_dinopartId.push('".$parceiro_id."'); var widgetHeight='".$list_h."px';</script><br /><script type='text/javascript' src='http://www.dino.com.br/embed/pagedlist.js'></script></div>";
            
            return $posts;
        }
        
        return $posts;
    }
}

function dino_plugin_query_parser( $q ) {
    $pp = get_page_by_title(get_option('dino_plugin_page_title'));
    $the_page_name = $pp->post_name;
    $the_page_id = get_option('dino_plugin_page_id');
    
    $ppl = get_page_by_title(get_option('dino_plugin_pageList_title'));
    $the_pageList_name = $ppl->post_name;
    $the_pageList_id = get_option('dino_plugin_pageList_id');
    
    $qv = $q->query_vars;
    
    if (!$q->did_permalink AND ( isset( $q->query_vars['page_id'] ) ) AND ( intval($q->query_vars['page_id']) == $the_page_id ) ) {
        $q->set('dino_plugin_page_is_called', true);        
        return $q;
    } elseif (isset($q->query_vars['pagename']) AND (($q->query_vars['pagename'] == $the_page_name) OR ($_pos_found = strpos($q->query_vars['pagename'], $the_page_name.'/') === 0))) {
        $q->set('dino_plugin_page_is_called', true);        
        return $q;
    } elseif (!$q->did_permalink AND (isset( $q->query_vars['page_id'])) AND (intval($q->query_vars['page_id']) == $the_pageList_id)) {
        $q->set('dino_plugin_page_is_called', false);
        $q->set('dino_plugin_list_is_called', true);        
        return $q;
    } elseif (isset( $q->query_vars['pagename']) AND (($q->query_vars['pagename'] == $the_pageList_name) OR ($_pos_found = strpos($q->query_vars['pagename'], $the_pageList_name.'/') === 0))) {
        $q->set('dino_plugin_list_is_called', true);        
        return $q;
    } else {
        $q->set('dino_plugin_list_is_called', false);
        return $q;
    }
}

function dino_admin_menu() {
    add_options_page('DINO - WP Plugin Settings', 'DINO - WP', 'administrator', __FILE__, 'dino_setting_page', plugins_url('/images/icon.png', _FILE_));
    add_action('admin_init', 'register_dinosettings');
}

function register_dinosettings() {
	register_setting( 'dino_settings_group', 'dino_plugin_option' );
    register_setting( 'dino_settings_group', 'dino_plugin_option_css' );
    register_setting( 'dino_settings_group', 'dino_plugin_list' );
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

function dinopagelistLink()
{
    global $wpdb;
    $pageid = get_option('dino_plugin_pageList_id');
    $actual_link = "http://$_SERVER[HTTP_HOST]?page_id=$pageid";
    return $actual_link;
}

class wctest{
    public function __construct(){
        if(is_admin()){
	    add_action('admin_menu', array($this, 'add_plugin_page'));

	    add_action('admin_init', array($this, 'page_init'));
        
        function pw_load_scripts() {
            wp_enqueue_style( 'dinoadmin-css', plugins_url( 'dino-wp/css/dinoAdmin.css' , dirname(__FILE__) ) );
            
            wp_enqueue_script( 'bootstrap-js', plugins_url( 'dino-wp/js/bootstrap.min.js' , dirname(__FILE__) ) );
            wp_enqueue_script( 'jquery-1102-js', plugins_url( 'dino-wp/js/jquery-1.10.2.min.js' , dirname(__FILE__) ) );
            wp_enqueue_script( 'dinoadmin-js', plugins_url( 'dino-wp/js/dinoAdmin.js' , dirname(__FILE__) ) );
        }

        if (isset($_GET['page']) && ($_GET['page'] == 'dino_setting_page'))
        {
         add_action('admin_enqueue_scripts', 'pw_load_scripts');
        }
       
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

            <div id="dinoCaixa">
                <ul class="nav nav-tabs" id="dino-tabs">
                    <li class="active"><a href="#dino-geral" data-toggle="tab">Geral</a></li>
                    <li><a href="#dino-aparencia" data-toggle="tab">Aparência</a></li>
                    <li><a href="#dino-lista" data-toggle="tab">Listagem</a></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="dino-geral"><?php do_settings_sections('dino-setting-admin');?></div>
                    <div class="tab-pane" id="dino-aparencia"><?php do_settings_sections('dino-setting-admin-css');?></div>
                    <div class="tab-pane" id="dino-lista"><?php do_settings_sections('dino-setting-admin-list');?></div>
                </div>
            </div>

	        <?php submit_button(); ?>
	    </form>
	</div>
<?php
    }
	
    public function page_init(){		
	register_setting('dino_option_group', 'dino_plugin_option');
    register_setting('dino_option_group', 'dino_plugin_option_css');
    register_setting('dino_option_group', 'dino_plugin_list');
		
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
	    '<div id="classes">Classes<ul><li>.dinotitulo</li><li>.dinoresumo</li><li>.dinolocal</li><li>.dinodata</li><li>.dinocorpo</li><li>.dinolink</li><li>.dinoarquivos</li></ul></div>', 
	    array($this, 'create_css_field'), 
	    'dino-setting-admin-css',
	    'sessao_aparencia'			
	);

    add_settings_section(
	    'sessao_lista',
	    'Listagem',
	    array($this, 'print_section_lista'),
	    'dino-setting-admin-list'
	);
    
        add_settings_field(
	        'dino_plugin_list', 
	        '', 
	        array($this, 'create_list_field'), 
	        'dino-setting-admin-list',
	        'sessao_lista'			
	    );
    	
    }
	
    public function print_section_aparencia(){
	print 'CSS:';
    }

    public function print_section_lista(){
	print 'Listagem:';
    }

    public function print_section_info(){
	print "Informações";
    }

    public function print_pagina_noticia(){
	print '<a href="'.dinopagelink().'">'.dinopagelink().'</a>';
    }
	
    public function create_css_field(){
        $op = get_option('dino_plugin_option_css');

        $cssLivre = "";
    $cssTitulo = "display:none;";
    $cssResumo = "color:#808080; margin-top:5px; display:inline;";
    $cssLocal = "color:#08c; font-weight:bold;";
    $cssData = "font-weight:bold;";
    $cssCorpo = "";
    $cssLink = "";
    $cssArquivos = "float:right; margin:3%; width:40%;";

    ?>
        <div>
            <h3>CSS Livre <span class="restaurar">[restaurar]</span></h3>
            <textarea style="width:100%; width: 80%; height:100px;" name="dino_plugin_option_css[Livre]" id="dinocss1"><?php echo $op["Livre"] ?></textarea>
            <input type="hidden" value="<?php echo $cssLivre ?>" class="padrao"/>
        </div>

        <div>
            <h3>Título <span class="restaurar">[restaurar]</span></h3>
            <textarea style="width:100%; width: 80%; height:30px;" name="dino_plugin_option_css[Titulo]" id="dinocss2"><?php echo $op["Titulo"] ?></textarea>
            <input type="hidden" value="<?php echo $cssTitulo ?>" class="padrao"/>
        </div>

        <div>
            <h3>Resumo <span class="restaurar"> [restaurar]</span></h3>
            <textarea style="width:100%; width: 80%; height:30px;" name="dino_plugin_option_css[Resumo]" id="dinocss3"><?php echo $op["Resumo"]?></textarea>
            <input type="hidden" value="<?php echo $cssResumo ?>" class="padrao"/>
        </div>

        <div>
            <h3>Local <span class="restaurar"> [restaurar]</span></h3>
            <textarea style="width:100%; width: 80%; height:30px;" name="dino_plugin_option_css[Local]" id="dinocss4"><?php echo $op["Local"]?></textarea>
            <input type="hidden" value="<?php echo $cssLocal ?>" class="padrao"/>
        </div>

        <div>
            <h3>Data <span class="restaurar"> [restaurar]</span></h3>
            <textarea style="width:100%; width: 80%; height:30px;" name="dino_plugin_option_css[Data]" id="dinocss5"><?php echo $op["Data"]?></textarea>
            <input type="hidden" value="<?php echo $cssData ?>" class="padrao"/>
        </div>

        <div>
            <h3>Corpo <span class="restaurar"> [restaurar]</span></h3>
            <textarea style="width:100%; width: 80%; height:30px;" name="dino_plugin_option_css[Corpo]" id="dinocss6"><?php echo $op["Corpo"]?></textarea>
            <input type="hidden" value="<?php echo $cssCorpo ?>" class="padrao"/>
        </div>

        <div>
            <h3>Link <span class="restaurar"> [restaurar]</span></h3>
            <textarea style="width:100%; width: 80%; height:30px;" name="dino_plugin_option_css[Link]" id="dinocss7"><?php echo $op["Link"]?></textarea>
            <input type="hidden" value="<?php echo $cssLink ?>" class="padrao"/>
            <br/>
            <label>Mostrar Link <input type="checkbox" name="dino_plugin_option_css[MostrarLink]" <?php checked( $op["MostrarLink"] == 'on',true); ?> /></label>
        </div>

        <div>
            <h3>Arquivos (imagem, video) <span class="restaurar"> [restaurar]</span></h3>
            <textarea style="width:100%; width: 80%; height:30px;" name="dino_plugin_option_css[Arquivos]" id="dinocss8"><?php echo $op["Arquivos"]?></textarea>
            <input type="hidden" value="<?php echo $cssArquivos ?>" class="padrao"/>
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

    public function create_list_field(){
        $op_info = get_option('dino_plugin_list');
        $pagelink = dinopagelistLink();
    ?>
        <div>
            <h3>Página da Listagem</h3>
            <a href="<?php echo $pagelink ?>"><?php echo $pagelink ?></a>
        </div>

        <div>
            <h3>Altura da Lista</h3>
            <input type="text" name="dino_plugin_list[Height]" id="dinolist1" value="<?php echo $op_info["Height"]?>" />
            
        </div>
        
        <div>
            <h3>Largura da Lista</h3>
            <input type="text" name="dino_plugin_list[Width]" id="dinolist2" value="<?php echo $op_info["Width"]?>" />
            
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
