<?php

/** 
 * Plugin Name: Tematres WP
 * Plugin URI: https://github.com/becahp
 * Description: Integração entre WP e Tematres
 * Version: 1.0
 * Author: Rebeca Moura e Lucas Rodrigues
 * Author URI: https://github.com/becahp
 */

//Definicoes
define('TEMATRES_WP_PATH', plugin_dir_path(__FILE__) . '/');
define('TEMATRES_WP_JS_PATH', plugin_dir_path(__FILE__) . 'js/');
define('TEMATRES_WP_JS_URL', plugin_dir_url(__FILE__) . 'js/');
define('TEMATRES_WP_CSS_PATH', plugin_dir_path(__FILE__) . 'css/');
define('TEMATRES_WP_CSS_URL', plugin_dir_url(__FILE__) . 'css/');

include "functions-api.php";

/**
 * Registro dos scripts usados nas páginas
 */
function tematres_wp_style_scripts()
{
    $ver = time();

    wp_register_style('css_tematres_wp', TEMATRES_WP_CSS_URL . 'tematres-wp.css', false, $ver);
    wp_enqueue_style('css_tematres_wp');

    wp_register_style('css_select2', TEMATRES_WP_CSS_URL . 'select2.min.css', false, $ver);
    wp_enqueue_style('css_select2');

    wp_enqueue_script('js_tematres_wp', TEMATRES_WP_JS_URL . 'tematres-wp.js', array('jquery', 'js_select2'), $ver);
    // já adicionando a url pro ajax
    //wp_localize_script('js_tematres_wp', 'my_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

    wp_enqueue_script('js_select2', TEMATRES_WP_JS_URL . 'select2.min.js', array(), $ver);
}
add_action('wp_enqueue_scripts', 'tematres_wp_style_scripts');

/**
 * Registro dos scripts usados nas páginas
 */
function tematres_wp_style_scripts_admin()
{
    $ver = time();

    wp_register_style('css_tematres_wp', TEMATRES_WP_CSS_URL . 'tematres-wp.css', false, $ver);
    wp_enqueue_style('css_tematres_wp');

    wp_enqueue_script('js_tematres_wp', TEMATRES_WP_JS_URL . 'tematres-wp.js', array(), $ver);
    // já adicionando a url pro ajax
    //wp_localize_script('js_tematres_wp', 'my_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

    //wp_enqueue_script('js_select2', TEMATRES_WP_JS_URL . 'select2.min.js', array(), $ver);
}
add_action('admin_enqueue_scripts', 'tematres_wp_style_scripts_admin');

/**
 * Função que adiciona o menu ao Painel
 */
function tematres_wp_admin_menu()
{
    add_menu_page(
        __('Tematres WP', 'tematres-wp-plugin'),
        __('Tematres WP', 'tematres-wp-plugin'),
        'manage_options', //'edit_posts', //'administrator'
        'tematres-wp-plugin',
        'tematres_wp_admin_page',
        'dashicons-chart-area',
        26
    );
}
add_action('admin_menu', 'tematres_wp_admin_menu');

function tematres_wp_admin_page()
{
    // set this var to be used in the settings-display view
    // $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
    if (isset($_GET['error_message'])) {
        add_action('admin_notices', array(
            'pagina_config_mensagem_erro'
        ));
        do_action('admin_notices', $_GET['error_message']);
    }
    require_once 'pagina-configuracao-display.php';
}

function pagina_config_mensagem_erro($error_message)
{
    switch ($error_message) {
        case '1':
            $message       = __('There was an error adding this setting. Please try again.  If this persists, shoot us an email.', 'tematres-wp-plugin');
            $err_code      = esc_attr('pagina_config_tematres_url');
            $setting_field = 'pagina_config_tematres_url';
            break;
    }

    $type = 'error';
    add_settings_error($setting_field, $err_code, $message, $type);
}


/**
 * Função registerAndBuildFields chamada de registrar_construir_campos
 * https://blog.wplauncher.com/create-wordpress-plugin-settings-page/
 * https://github.com/wplauncher/settings-page
 * This file is where you define what fields you want to include in your settings form and it hooks up to another function that handles saving and pre-population of your form if users have already filled it out.
 * Pay close attention to the wp_data parameter in this function as it determines how you want this field to be treated by WordPress. On settings pages, you want to set this parameter as an option because plugin settings are typically applicable globally to your WordPress site. However, if you used this function in a custom post type, you would want to use post_meta, so that the information was attached to a post
 */
function pagina_config_registrar_construir_campos()
{
    /**
     * First, we add_settings_section. This is necessary since all future settings must belong to one.
     * Second, add_settings_field
     * Third, register_setting
     */

    add_settings_section(
        // ID used to identify this section and with which to register options
        'pagina_config_secao',
        // Title to be displayed on the administration page
        'URL do Tematres',
        // Callback used to render the description of the section
        'pagina_config_mensagem_geral',
        // Page on which to add this section of options
        'pagina_config'
    );

    unset($args);

    $args = array(
        'type' => 'input',
        'subtype' => 'url',
        'id' => 'pagina_config_tematres_url',
        'name' => 'pagina_config_tematres_url',
        'required' => 'true',
        'placeholder' => 'Insira uma URL',
        'size' => 70,
        'get_options_list' => '',
        'value_type' => 'normal',
        'wp_data' => 'option'
    );

    add_settings_field(
        'pagina_config_tematres_url',
        'Tematres URL:',
        'pagina_config_renderizar_campos',
        'pagina_config',
        'pagina_config_secao',
        $args
    );
    
    register_setting('pagina_config', 'pagina_config_tematres_url');

    #adicionando checkbox
    #https://wordpress.stackexchange.com/questions/328648/saving-multiple-checkboxes-with-wordpress-settings-api
    #https://wordpress.stackexchange.com/questions/110503/how-to-use-checkbox-in-custom-option-page-using-the-setting-api
    #http://qnimate.com/add-checkbox-using-wordpress-settings-api/

    add_settings_section("section-posts-types", "Posts que serão aplicado as Tags", null, "posts-types");
    add_settings_field('post_types', "Selecione os Posts:", "post_types_checkbox_field_1_render", "posts-types", "section-posts-types");  
    register_setting("section-posts-types", 'post_types');   
    
}

function post_types_checkbox_field_1_render() {

    $options = get_option( 'post_types', [] );

    $post_types_checkbox_field_1 = isset( $options['post_types_checkbox_field_1'] )
        ? (array) $options['post_types_checkbox_field_1'] : [];
    
    
    // Função que retorna todos os custom post types
    $args = array(
        'public'   => true,
        '_builtin' => false,
    );
    $output = 'objects'; // names or objects, note names is the default
    $operator = 'and'; // 'and' or 'or'
    $post_types = get_post_types($args, $output, $operator);
    // Adiciona o post comum também no inicio
    array_unshift($post_types, get_post_types( [], 'objects' )["post"]);
    foreach ($post_types as $post) {
        $slug = $post->name;
        $name = $post->label;
    ?>
    <input type='checkbox' id='<?php echo $slug?>' name='post_types[post_types_checkbox_field_1][]' <?php checked( in_array( $slug, $post_types_checkbox_field_1 ), 1 ); ?> value='<?php echo $slug?>'>
        <label for="<?php echo $slug?>"><?php echo $name?></label>
        <br>
    <?php
    }
}

function pagina_config_mensagem_geral()
{
    echo '<p>Essa configuração se aplica a todas as funcionalidades do plugin Tematres WP.</p>';
}

function pagina_config_renderizar_campos($args)
{
    /* EXAMPLE INPUT
    'type'      => 'input',
    'subtype'   => '',
    'id'    => $this->plugin_name.'_example_setting',
    'name'      => $this->plugin_name.'_example_setting',
    'required' => 'required="required"',
    'get_option_list' => "",
    'value_type' = serialized OR normal,
    'wp_data'=>(option or post_meta),
    'post_id' =>
    */

    if ($args['wp_data'] == 'option') {

        $wp_data_value = get_option($args['name']);

    } elseif ($args['wp_data'] == 'post_meta') {

        $wp_data_value = get_post_meta($args['post_id'], $args['name'], true);
    }

    switch ($args['type']) {

        case 'input':
            $value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;

            if ($args['subtype'] != 'checkbox') {

                $prependStart = (isset($args['prepend_value'])) ? '<div class="input-prepend"> <span class="add-on">' . $args['prepend_value'] . '</span>' : '';
                $prependEnd   = (isset($args['prepend_value'])) ? '</div>' : '';
                $step         = (isset($args['step'])) ? 'step="' . $args['step'] . '"' : '';
                $min          = (isset($args['min'])) ? 'min="' . $args['min'] . '"' : '';
                $max          = (isset($args['max'])) ? 'max="' . $args['max'] . '"' : '';

                if (isset($args['disabled'])) {
                    // hide the actual input bc if it was just a disabled input the info saved in the database would be wrong - bc it would pass empty values and wipe the actual information
                    echo $prependStart . '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '_disabled" ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '_disabled" size="40" disabled value="' . esc_attr($value) . '" /><input type="hidden" id="' . $args['id'] . '" ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '" size="40" value="' . esc_attr($value) . '" />' . $prependEnd;
                } else {

                    // O CAMPO NORMAL É RENDERIZADO AQUI

                    //echo $prependStart . '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '" "' . $args['required'] . '" ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '" size="40" value="' . esc_attr($value) . '" />' . $prependEnd;

                    echo $prependStart . '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '" required="' . $args['required'] . '" ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '" size="' . $args['size'] . '" placeholder="' . $args['placeholder'] . '" value="' . esc_attr($value) . '" />' . $prependEnd;
                }
            } else {
                $checked = ($value) ? 'checked' : '';
                echo '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '" "' . $args['required'] . '" name="' . $args['name'] . '" size="40" value="1" ' . $checked . ' />';
            }
            break;
        default:
            # code...

            break;
    }
}
add_action('admin_init', 'pagina_config_registrar_construir_campos');

//--------------------------------------------------------------------- API do tematres
add_shortcode('shortcode_show_tags_tematres', 'show_tags_tematres');
#Ex: [shortcode_show_tags_tematres] 
function show_tags_tematres()
{
?>
    <form action="<?php echo get_permalink() ?>" method="post">
        <div>
            <label for="field-name"> Informe o Termo:</label>
            <?php
            $termo = $_POST['termo'];
            if (empty($termo)) {
                echo "<input type=\"text\" name=\"termo\" id=\"termo\" minlength=\"2\" placeholder=\"laranja\" required />";
            } else {
                echo "<input type=\"text\" name=\"termo\" id=\"termo\" minlength=\"2\" value=\"$termo\" placeholder=\"laranja\" required />";
            }
            ?>
        </div>
        <div>
            <button type="submit">Enviar</button>
        </div>
    </form>
    <?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // collect value of input field
        $termo = $_POST['termo'];
        if (empty($termo)) {
            echo "Termo vazio";
        } else {
            $urlTematres = get_option('pagina_config_tematres_url');
            echo "<p>Query de busca é \"$termo\"</p>";
            $urlBusca = $urlTematres . "?task=search&arg=" . strtolower($termo);
            echo "<p>URL de busca é: $urlBusca</p>";

            $xml = simplexml_load_file($urlBusca)->result;
            #var_dump($xml);
            //carrega o arquivo XML e retornando um Array
            $termos = array();
            foreach ($xml->term as $item) {
                array_push($termos, $item->string);
            }

            for ($i = 0; $i < count($termos); ++$i) {
                echo "<strong>Termo $i:</strong> " . $termos[$i] . "<br/>";
            }
        }
    }
}

//--------------------------------------------------------------------- Editando as Tags

// Remove Categories and Tags
add_action('init', 'myprefix_remove_tax');
function myprefix_remove_tax()
{
    //register_taxonomy('category', array());
    register_taxonomy('post_tag', array());
}

//--------------------------------------------------------------------- criando a metabox
/*
 * Add
 */
function rudr_add_new_tags_metabox()
{
    $id = 'tematres-wp-integration_tag'; // it should be unique
    $heading = 'Tags'; // meta box heading
    $callback = 'rudr_metabox_content'; // the name of the callback function

    // $args = array(
    //     'public'   => true,
    //     '_builtin' => false,
    // );

    // $output = 'names'; // names or objects, note names is the default
    // $operator = 'and'; // 'and' or 'or'

    // // Função que retorna todos os custom post types
    // $post_type = get_post_types($args, $output, $operator);

    // // Adiciona o post comum também
    // array_push($post_type, 'post');

    $post_type = get_option('post_types')["post_types_checkbox_field_1"]; 

    $position = 'side';
    $pri = 'low'; // priority, 'default' is good for us
    add_meta_box($id, $heading, $callback, $post_type, $position, $pri);
}
add_action('admin_menu', 'rudr_add_new_tags_metabox');

/*
 * Fill
 */
function rudr_metabox_content($post)
{

    // // get all blog post tags as an array of objects
    // $all_tags = get_terms(array('taxonomy' => 'post_tag', 'hide_empty' => 0));

    // // get all tags assigned to a post
    // $all_tags_of_post = get_the_terms($post->ID, 'post_tag');

    // // create an array of post tags ids
    // $ids = array();
    // if ($all_tags_of_post) {
    //     foreach ($all_tags_of_post as $tag) {
    //         $ids[] = $tag->term_id;
    //     }
    // }

    // HTML
    //echo '<div id="taxonomy-post_tag" class="categorydiv">';
    // echo '<input type="hidden" name="tax_input[post_tag][]" value="0" />';
    // echo '<ul>';
    // foreach( $all_tags as $tag ){
    // 	// unchecked by default
    // 	$checked = "";
    // 	// if an ID of a tag in the loop is in the array of assigned post tags - then check the checkbox
    // 	if ( in_array( $tag->term_id, $ids ) ) {
    // 		$checked = " checked='checked'";
    // 	}
    // 	$id = 'post_tag-' . $tag->term_id;
    // 	echo "<li id='{$id}'>";
    // 	echo "<label><input type='checkbox' name='tax_input[post_tag][]' id='in-$id'". $checked ." value='$tag->slug' /> $tag->name</label><br />";
    // 	echo "</li>";
    // }
    // echo '</ul></div>'; // end HTML

    //echo '<select class="tematres-wp-integration-escolhas" name="escolha_tags" multiple="multiple">';
    //echo '<option value="">Selecione as Tags</option>';
    //echo '</select>';
    //echo '</div>';
    //echo do_shortcode('[shortcode_teste_select]');
    echo '<div id="taxonomy-post_tag" class="categorydiv">';
    echo '<select id="escolha_tags" class="tematres-wp-integration-escolhas" name="escolha_tags" multiple="multiple">';
    echo '<option value="">Selecione as Tags</option>';
    echo '</select>';
    echo '</div>';
}

add_shortcode('shortcode_teste_select', 'teste_select');
#Ex: [shortcode_teste_select] 
function teste_select()
{
    echo '<div id="taxonomy-post_tag" class="categorydiv" style="height: 75px;">';
    echo '<p>';
    echo '<select id="escolha_tags" class="tematres-wp-integration-escolhas" name="escolha_tags" multiple="multiple">';
    echo '<option value="">Selecione as Tags</option>';
    echo '</select>';
    echo '</p>';
    echo '</div>';
}
