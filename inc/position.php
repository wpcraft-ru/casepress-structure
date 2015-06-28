<?php

/*
Подразделения
*/
class PositionCP_Singletone {
private static $_instance = null;

    private function __construct() {

        add_action('init', array($this, 'cpt'));
        add_action( 'add_meta_boxes', array( &$this, 'add_meta_boxes_callback' ) );
        add_action( 'save_post', array( &$this, 'save' ) );
        add_filter( 'the_content', array( &$this, 'view' ) );
    }
    
    
        function view($content){
        
        if(! is_singular('position')) return $content;
        $post = get_post();
        
        ob_start();
        ?>
            <section id="position_data" class="section_cp">
                <h1>Основные данные</h1>
                <ul>
                    <?php
                        if($post->post_parent) {
                            echo "
                            <li>
                                <span>Соответствующее подразделение: </span>
                                <a href=" . get_permalink ($post->post_parent) . ">" . get_the_title ($post->post_parent) . "</a>
                            </li>";
                        }
                    ?>
                    <li>
                        <span>Ответственный: </span>
                        <span><?php echo get_post_meta($post->ID, 'responsible',true); ?></span>
                    </li>
                    <li>
                        <span>Телефон: </span>
                        <span><?php echo get_post_meta($post->ID, 'phone',true); ?></span>
                    </li>
                </ul>
            </section>
            <section id="product_s" class="section_cp">
                <h1>Продукт</h1>
                <?php echo get_post_meta($post->ID, 'product',true); ?>           
            </section>
            <section id="indicators_s" class="section_cp">
                <h1>Показатели</h1>
                <?php echo get_post_meta($post->ID, 'indicators',true); ?>           
            </section>
        <?php
        
        $html = ob_get_contents();
        ob_get_clean();
        return $html . $content;
    }

    function add_meta_boxes_callback(){
        add_meta_box('position_data', __('Data', 'casepress'), array(&$this, 'data_callback'), 'position', 'normal');
        add_meta_box('position_product_mb', __('Product', 'casepress'), array(&$this, 'product_mb_callback'), 'position', 'normal');
        add_meta_box('position_indicators_mb', __('indicators', 'casepress'), array(&$this, 'indicators_mb_callback'), 'position', 'normal');
    }
    
    
    function data_callback(){

        wp_nonce_field( basename( __FILE__ ), 'position_data_nonce' );
        
        $post = get_post();
        $responsible = get_post_meta($post->ID, 'responsible',true);
        $phone = get_post_meta($post->ID, 'phone',true);
        ?>
            <p id="position_responsible_wrapper">
                <label for="responsible">Ответственный</label><br/>
                <small>Укажите ФИО ответственного</small><br/>
                <input type="text" name="responsible" id="responsible" class="field_cp" value="<?php echo $responsible ?>" size="50">
            </p>
           <p id="phone_wrapper">
                <label for="phone">Телефон</label><br/>
                <small>Укажите телефон поста</small><br/>
                <input type="text" name="phone" id="phone" class="field_cp" value="<?php echo $phone ?>" size="50">
            </p>         
            <p id="parent_wrapper">
                <label for="parent">Подразделение</label><br/>
                
                <?php
            
                    wp_dropdown_pages( array(
                        'depth'            => 0,
                        'child_of'         => 0,
                        'selected'         => $post->post_parent,
                        'echo'             => 1,
                        'name'             => 'post_parent',
                        'show_option_none' => 'Подразделение',
                        'post_type'     => 'branch',
                        'value_field'      => 'ID', // поле для значения value e тега option
                    ));
                
                ?>
            </p>   
        <?php
    }
    
    
    function indicators_mb_callback(){
                   
        $post = get_post();

        $indicators = get_post_meta($post->ID, 'indicators',true);

        echo "<p>Опишите показатели поста</p>";
        
        wp_editor(
            $indicators,
            'indicators',
            $settings = array(
                'media_buttons' => 1,
                'textarea_name' => 'indicators', //нужно указывать!
                'textarea_rows' => 10,
            ));
    
    }
    
    function product_mb_callback(){
                   
        $post = get_post();

        $product = get_post_meta($post->ID, 'product',true);

        echo "<p>Опишите ценный конечный продукт</p>";
        
        wp_editor(
            $product,
            'product',
            $settings = array(
                'media_buttons' => 1,
                'textarea_name' => 'product', //нужно указывать!
                'textarea_rows' => 10,
            ));
    
    }

    
    
    
    function save($post_id){
        
        // if autosave then cancel
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
        // check wpnonce
        if ( !isset( $_POST['position_data_nonce'] ) || !wp_verify_nonce( $_POST['position_data_nonce'], basename( __FILE__ ) ) ) return $post_id;
        //user can?
        if ( !current_user_can( 'edit_post', $post_id ) ) return $post_id;
        //go
        $post = get_post($post_id);
        
        update_post_meta($post_id, 'responsible', esc_attr($_POST['responsible']));
        update_post_meta($post_id, 'phone', esc_attr($_POST['phone']));
        update_post_meta($post_id, 'product', esc_attr($_POST['product']));
        update_post_meta($post_id, 'indicators', esc_attr($_POST['indicators']));
   
    }

function cpt() {

	$labels = array(
		'name'                => __( 'Positions', 'casepress' ),
		'singular_name'       => __( 'Position', 'casepress' ),
		'menu_name'           => __( 'Positions', 'casepress' ),
		'parent_item_colon'   => __( 'Parent', 'casepress' ),
		'all_items'           => __( 'Positions', 'casepress' ),
		'view_item'           => __( 'View', 'casepress' ),
		'add_new_item'        => __( 'Add Position', 'casepress' ),
		'add_new'             => __( 'New Position', 'casepress' ),
		'edit_item'           => __( 'Edit', 'casepress' ),
		'update_item'         => __( 'Update', 'casepress' ),
		'search_items'        => __( 'Search', 'casepress' ),
		'not_found'           => __( 'Not found', 'casepress' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'casepress' ),
	);
	$args = array(
		'description'         => __( 'Description Position', 'casepress' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'comments' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => 'edit.php?post_type=branch',
		'show_in_admin_bar'   => true,
		'menu_position'       => 20,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
	);
	register_post_type( 'position', $args );

}

    
    
protected function __clone() {
	// ограничивает клонирование объекта
}

static public function getInstance() {
	if(is_null(self::$_instance))
	{
	self::$_instance = new self();
	}
	return self::$_instance;
}

} $PositionCP = PositionCP_Singletone::getInstance();