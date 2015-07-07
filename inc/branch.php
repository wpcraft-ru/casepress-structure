<?php

/*
Подразделения
*/
class BrancheCP_Singletone {
private static $_instance = null;

    private function __construct() {

        add_action('init', array($this, 'cpt'));
        add_action( 'add_meta_boxes', array( &$this, 'add_meta_boxes_callback' ) );
        add_action( 'save_post', array( &$this, 'save' ) );
        add_filter( 'the_content', array( &$this, 'view' ) );

    }



    function view($content){
        
        if(! is_singular('branch')) return $content;
        $post = get_post();
        $responsible = get_post_meta($post->ID, 'responsible',true);
        $process_category_id = get_post_meta($post->ID, 'process_category_id',true);

        ob_start();
        ?>
            <section id="branch_data" class="section_cp">
                <h1>Основные данные</h1>
                <ul>
                    <?php
                        if($post->post_parent) {
                            echo "
                            <li>
                                <span>Вышестоящее подразделение: </span>
                                <a href=" . get_permalink ($post->post_parent) . ">" . get_the_title ($post->post_parent) . "</a>
                            </li>";
                        }
                    ?>
                    <li>
                        <span>Ответственный: </span>
                        <a href="<?php echo get_permalink ($responsible) ?>"><?php echo get_the_title ($responsible) ?></a>
                    </li>
                    <li>
                        <span>Телефон: </span>
                        <span><?php echo get_post_meta($post->ID, 'branche_phone',true); ?></span>
                    </li>
                </ul>
            </section>
            <section id="branche_product_s" class="section_cp">
                <h1>Продукт</h1>
                <?php echo get_post_meta($post->ID, 'branche_product',true); ?>           
            </section>
            <section id="branche_indicators_s" class="section_cp">
                <h1>Показатели</h1>
                <?php 
                    $kpi = get_post_meta($post->ID, 'branche_indicators',true);
                    $kpi = html_entity_decode($kpi);
                    echo wpautop($kpi); 
                ?> 
            </section>
        <?php
            //Получаем дочерние подразделения
            $childrens = get_children( array(
                    'post_parent' => $post->ID,
                    'post_type'   => 'branch', 
                    'numberposts' => 33,
                    'post_status' => 'publish'
                ));
        
            if($childrens) {
                ?>
                    <section id="branche_subbranches" class="section_cp">
                        <h1>Подразделения</h1>
                        <ul>
                        <?php 
                            foreach($childrens as $item) {
                                ?>
                                    <li>
                                        <a href="<?php echo get_permalink ($item->ID); ?>"><?php echo get_the_title($item->ID); ?></a>
                                    </li>
                                <?php
                            }
                        ?>   
                        </ul>
                    </section>
                <?php
            }
        
        //Получаем дочерние позиции
        $positions = get_children( array(
                'post_parent' => $post->ID,
                'post_type'   => 'position', 
                'numberposts' => 99,
                'post_status' => 'publish'
            ));

        if($positions) {
            ?>
                <section id="branche_positions" class="section_cp">
                    <h1>Посты</h1>
                    <ul>
                        <?php 
                            foreach($positions as $item) {
                                ?>
                                    <li>
                                        <a href="<?php echo get_permalink ($item->ID); ?>"><?php echo get_the_title($item->ID); ?></a>
                                    </li>
                                <?php
                            }
                        ?>   
                    </ul>
                </section>
            <?php
        }
        
        //Выводим список процессов
        $processes = get_posts( array(
            'tax_query' => array(
                array(
                    'taxonomy' => 'process_category',
                    'field' => 'id',
                    'terms' => $process_category_id
                )
             ),
            'post_type'   => 'process', 
            'numberposts' => 99,
            'post_status' => 'publish'
        ));

        if($processes) {
            ?>
                <section id="branche_process_list" class="section_cp">
                    <h1>Бизнес-процессы</h1>
                    <ul>
                        <?php 
                            foreach($processes as $item) {
                                ?>
                                    <li>
                                        <a href="<?php echo get_permalink ($item->ID); ?>"><?php echo get_the_title($item->ID); ?></a>
                                    </li>
                                <?php
                            }
                        ?>   
                    </ul>
                </section>
            <?php
        }
        
        
        $html = ob_get_contents();
        ob_get_clean();
        return $html . $content;
    }
    
    
    
    function add_meta_boxes_callback(){
        add_meta_box('branche_data', __('Data for branche', 'casepress'), array(&$this, 'branche_data_callback'), 'branch', 'normal');
        add_meta_box('branche_product_mb', __('Product by branche', 'casepress'), array(&$this, 'branche_product_mb_callback'), 'branch', 'normal');
        add_meta_box('branche_indicators_mb', __('indicators for branche', 'casepress'), array(&$this, 'branche_indicators_mb_callback'), 'branch', 'normal');
    }
    
    function branche_data_callback(){

        wp_nonce_field( basename( __FILE__ ), 'branche_data_nonce' );
        
        $post = get_post();
        $responsible = get_post_meta($post->ID, 'responsible',true);
        $branche_phone = get_post_meta($post->ID, 'branche_phone',true);
        $process_category_id = get_post_meta($post->ID, 'process_category_id',true);
        ?>
            <p id="responsible_wrapper">
                <label for="responsible">Ответственный</label><br/>
                <small>Укажите пост ответственный за данное подразделение</small><br/>
                <select name="responsible" id="responsible">
                    <option value="">Посты</option>
                    <?php 
                        $positions = get_posts( array(
                            'post_type'   => 'position', 
                            'numberposts' => -1,
                            'post_status' => 'publish'
                        ));

                        foreach($positions as $item) {
                            
                            ?>
                                <option value="<?php echo $item->ID ?>" <?php selected( $responsible, $item->ID) ?>><?php echo get_the_title($item->ID)  ?></option>
                            <?php
                        }
                    ?>                        
                </select>
            </p>
           <p id="branche_phone_wrapper">
                <label for="branche_phone">Телефон</label><br/>
                <small>Укажите телефон подразделения</small><br/>
                <input type="text" name="branche_phone" id="branche_phone" class="field_cp" value="<?php echo $branche_phone ?>" size="50">
            </p>
            <p id="process_category_wrapper">
                <label for="process_category_id">Категория процессов</label><br/>
                <small>Выберите коллекцию процессов соответствующую данному подразделению</small><br/>
                 <?php
                    wp_dropdown_categories( array(
                        'show_option_all'    => 'Все категории',
                        'show_option_none'   => 'Не выбрано',
                        'show_count'         => 1,
                        'hide_empty'         => 0,
                        'echo'               => 1,
                        'selected'           => $process_category_id,
                        'hierarchical'       => 1,
                        'name'               => 'process_category_id',
                        'id'                 => 'process_category_id',
                        'class'              => 'postform',
                        'taxonomy'           => 'process_category',
                        'hide_if_empty'      => false,
                        'value_field'        => 'term_id', // значение value e option
                    ) );
                        ?>
            </p>
        <?php
    }
    
    
    function branche_indicators_mb_callback(){
                   
        $post = get_post();

        $branche_indicators = html_entity_decode(get_post_meta($post->ID, 'branche_indicators',true));

        echo "<p>Опишите показатели подразделения</p>";
        
        wp_editor(
            $branche_indicators,
            'brancheindicators',
            $settings = array(
                'media_buttons' => 1,
                'textarea_name' => 'branche_indicators', //нужно указывать!
                'textarea_rows' => 10,
            ));
    
    }
    
    function branche_product_mb_callback(){
                   
        $post = get_post();

        $branche_product = get_post_meta($post->ID, 'branche_product',true);

        echo "<p>Опишите ценный конечный продукт</p>";
        
        wp_editor(
            $branche_product,
            'brancheproduct',
            $settings = array(
                'media_buttons' => 1,
                'textarea_name' => 'branche_product', //нужно указывать!
                'textarea_rows' => 10,
            ));
    
    }

    
    
    
    function save($post_id){
        
        // if autosave then cancel
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
        // check wpnonce
        if ( !isset( $_POST['branche_data_nonce'] ) || !wp_verify_nonce( $_POST['branche_data_nonce'], basename( __FILE__ ) ) ) return $post_id;
        //user can?
        if ( !current_user_can( 'edit_post', $post_id ) ) return $post_id;
        //go
        $post = get_post($post_id);
        
        update_post_meta($post_id, 'responsible', esc_attr($_POST['responsible']));
        update_post_meta($post_id, 'branche_phone', esc_attr($_POST['branche_phone']));
        update_post_meta($post_id, 'branche_product', esc_attr($_POST['branche_product']));
        update_post_meta($post_id, 'branche_indicators', esc_attr($_POST['branche_indicators']));
        update_post_meta($post_id, 'process_category_id', esc_attr($_POST['process_category_id']));



        
    }


    function cpt() {

        $labels = array(
            'name'                => __( 'Branches', 'casepress' ),
            'singular_name'       => __( 'Branche', 'casepress' ),
            'menu_name'           => __( 'Branches', 'casepress' ),
            'parent_item_colon'   => __( 'Parent', 'casepress' ),
            'all_items'           => __( 'Branches', 'casepress' ),
            'view_item'           => __( 'View', 'casepress' ),
            'add_new_item'        => __( 'Add branche', 'casepress' ),
            'add_new'             => __( 'New Branche', 'casepress' ),
            'edit_item'           => __( 'Edit', 'casepress' ),
            'update_item'         => __( 'Update', 'casepress' ),
            'search_items'        => __( 'Search', 'casepress' ),
            'not_found'           => __( 'Not found', 'casepress' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'casepress' ),
        );
        $args = array(
            'description'         => __( 'Desciption Branche', 'casepress' ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'editor', 'comments', 'page-attributes', ),
            'hierarchical'        => true,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 20,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
        );
        register_post_type( 'branch', $args );

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

} $BrancheCP = BrancheCP_Singletone::getInstance();
