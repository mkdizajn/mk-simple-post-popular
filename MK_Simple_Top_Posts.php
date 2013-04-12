<?php
/*
Plugin Name: MK Simple Top Posts - native wordpress counter via custom fields
Plugin URI: http://mk-dizajn.com
Description: Adds custom field == counter to each post when viewed by user.
Version: 0.9
Author: MK dizajn
Author URI: http://mk-dizajn.com
Author Email: kpendic@gmail.com
License:

    Copyright 2010 - 2013 MK dizajn (kpendic@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


    /*--------------------------------------------------*/
    /* Widget
    /*--------------------------------------------------*/

class MK_popular_posts_simple_counter extends WP_Widget {

    /*--------------------------------------------------*/
    /* Constructor
    /*--------------------------------------------------*/
    
    function MK_popular_posts_simple_counter() {
    
        $widget_opts = array (
            'classname' => 'mk-popular-posts',
            'description' => __('Show the top posts based on page views!', 'mk-popular-posts')
        );      
        
        $this->WP_Widget('mk-popular-posts', __('MK::Top posts', 'mk-popular-posts'), $widget_opts);
        // load_plugin_textdomain('mk-popular-posts', false, dirname(plugin_basename( __FILE__ ) ) . 'lang/' );
        
    } // end constructor

    public function widget( $args, $instance ) { // here goes the widget output
        extract( $args );
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
        $c = ! empty( $instance['count'] ) ? '1' : '0'; // jel ima text ili ne!
        $d = ! empty( $instance['wide'] ) ? '1' : '0'; // jesu siroki ili ne thumbovi
        if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) ) $number = 10;
        $slikica = ( $d == "1" ) ? "dnevni" : "thumbic"; // mala ili velika slikica!


        echo $before_widget;
        echo $before_title . $title . $after_title; // title

        $r = new WP_Query( array( 'meta_key'=>'count', 'orderby' =>'meta_value_num', 'posts_per_page' => $number ));

        while( $r->have_posts() ) : $r->the_post();  ?>
         <div id="<?php echo $slikica; ?>" class="small">
            <a href="<?php the_permalink() ?>" rel="bookmark"><?php the_post_thumbnail(); ?></a>
            <?php if ($c == "1") { ?>
                <h3 class="title"><?php the_title(); ?></h3>
                <div class="excerpt"><?php the_excerpt(); ?></div>
                <?php } ?>
          </div>
        <?php endwhile;
        echo $after_widget;

    } // end widget

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['number'] = (int) $new_instance['number'];
        $instance['count'] = !empty($new_instance['count']) ? 1 : 0;
        $instance['wide'] = !empty($new_instance['wide']) ? 1 : 0;

        return $instance;
    }

    public function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'reset' => '' ) );
        ( isset( $instance[ 'title' ] ) ) ? $title = $instance[ 'title' ] : $title = "";
        $title = strip_tags($instance['title']);

        $number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
        $count = isset($instance['count']) ? (bool) $instance['count'] :false;
        $wide = isset($instance['wide']) ? (bool) $instance['wide'] :false;
        ?>

        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>">
            <?php _e( 'Title:', 'mk-popular-posts' ); ?>
        </label>
        <input class="widefat" 
            id="<?php echo $this->get_field_id( 'title' ); ?>" 
            name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

        <p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'How many posts to show:', 'mk-popular-posts' ); ?></label>
        <input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>

        <p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
        <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show title and excerpt?', 'mk-popular-posts' ); ?></label></p>

        <p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('wide'); ?>" name="<?php echo $this->get_field_name('wide'); ?>"<?php checked( $wide ); ?> />
        <label for="<?php echo $this->get_field_id('wide'); ?>"><?php _e( 'Are the thumbs wide?', 'mk-popular-posts' ); ?></label></p>

    <?php 
    } // end form

} // end class widget



    /*--------------------------------------------------*/
    /* Simple counter fn()
    /*--------------------------------------------------*/

function mkcount() {

    global $post; // get that post!

    if ( ! is_admin() ){ // we are on frontend not admin!
        if ( is_single( $post ) ){ // only trigger on single post!

            $counter_value = get_post_meta( $post->ID , 'count', true); // old value for count

            if( $counter_value != '' ) {
                update_post_meta( $post->ID , 'count', $counter_value+1 ); // UPDATE
            } else {
                add_post_meta( $post->ID , 'count', 1); // meta custom field value == to hide it on admin backend just prepand _.. like this: _count
            }
        }
    }
}


add_action( 'wp_head', 'mkcount' ); // attach it somewhere on post object

add_action( 'widgets_init', create_function( '' , 'register_widget( "MK_popular_posts_simple_counter" ); '));


?>