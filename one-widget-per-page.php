<?php

/*
Plugin Name: One Widget per Page
Plugin URI: http://pirex.com.br/wordpress-plugins
Description: Creates one widget for each page on your site. The widget will display page title in <h2> tags and a short description linked to the page
Author: Leo Germani
Version: 1.0
Author URI: http://pirex.com.br
*/ 

/*  Copyright 2007 Leo Germani  (email : leogermani@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


define( wSP_PREFIX, 'singlePage_WIDGET' );
define( wSP_MAJOR, 1 );
define( wSP_MINOR, 0 );
define( wSP_BUILD, 2 );


add_action('plugins_loaded', 'wSPInit' );

//-----------------------------------------------------------------------------

function wSPInit()
{
    if ( function_exists('register_sidebar') && function_exists('register_sidebar_widget') )
    {
	global $wpdb;
	
        $query=mysql_query("SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'page'");
	$q_total=mysql_num_rows($query);
	if ($q_total > 0) 
	
        {
            while ($fetch=mysql_fetch_array($query)) 
            {
                if ( $fetch['post_title'] != '' )
                {
                    $code = preg_replace('/[^a-z]+/i', '_', strtolower( $fetch['post_title'] ) );
                    $controlFunc = create_function( '', 'return wSPOption( ' . $fetch['ID'] . ' );' );
                    $widgetFunc = create_function( '$args', 'return wSPoutputPage( $args, '. $fetch['ID'] . ',"' . $fetch['post_title'] . '" );' );

                    register_widget_control( 'page - '. $fetch['post_title'], $controlFunc );
                    register_sidebar_widget( 'page - '. $fetch['post_title'], $widgetFunc, sanitize_title('page-'. $code) );
                }
            }
        }

        
        }
    }

    //------------------------------------------------------------------------

    function wSPOption( $section )
    {
        $optionName = wSP_PREFIX . $section . 'Options';
        $submitNotifierName = $section . 'submit';

        $options = get_option( $optionName );

        if ( isset( $_POST[$submitNotifierName] ) )
        {

		$newoption['title'] = $_POST[$optionName.'title'];
		$newoption['text'] = $_POST[$optionName.'text'];


            if ( $options != $newoption )
            {
                $options = $newoption;
                update_option( $optionName, $options);
            }
        }
        
	$title = $options['title'];
        $text = $options['text'];

        ?>
        <div>
        
	<label for="<?php echo $optionName; ?>title" style="line-height:35px;display:block;">Imagem de Título: <input type="text" id="<?php echo $optionName; ?>title" name="<?php echo $optionName; ?>title" value="<?php echo $title; ?>" /></label>
	<br><br>
	Enter the text to be displayed as page description.

            <label for="<?php echo $optionName; ?>text" style="line-height:35px;display:block;">Text:<BR>
            <textarea id="<?php echo $optionName; ?>text" name="<?php echo $optionName; ?>text"> <?php echo $text; ?></textarea></label>
            <?php
        
        
        ?>
        <input type="hidden" name="<?php echo $submitNotifierName; ?>" id="<?php echo $submitNotifierName; ?>" value="1" />
        </div>
        <?php
    }


    function wSPoutputPage( $args, $p_id, $p_title )
    {
        extract( $args );
        $optionName = wSP_PREFIX . $p_id . 'Options';

        $options = get_option( $optionName );
        

        echo $before_widget;
        ?>
	
	<? if ($options['title']=='') {?>
        	<h2 class="widget-single-page"><a href="<?php echo get_permalink( $p_id ); ?>"><?php echo $p_title; ?></a></h2>
	<? }else{ ?>
		<h2 class="widget-single-page"><a href="<?php echo get_permalink( $p_id ); ?>"><?php echo $options['title']; ?></a></h2>
	<?} ?>
		<div class="widget-single-page-div" id ="widget-single-page-<?php echo $p_id; ?>"><a href="<?php echo get_permalink( $p_id ); ?>"><?php echo $options['text']; ?></a></div>
	<?
			   
          echo $after_widget;
    }



?>
