<?php
/**
 * Plugin Name: Unused file checker
 * Plugin URI: http://www.wemessage.nl/
 * Description: Finds and removes unused files from uploads map
 * Author: Naberd @ Wemessage
 * Author URI: https://www.wemessage.nl/
 * Version: 1.0
 * Text Domain: wemessage_fix_files
 * Domain Path: /languages/
 *
 */
 
add_action( 'init', 'wemessage_fix_files_load_textdomain' ); 
function wemessage_fix_files_load_textdomain() {
    load_plugin_textdomain( 'wemessage_fix_files', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

add_action('admin_menu',  'wemessage_fix_files_admin_menu', 9);
function wemessage_fix_files_admin_menu() {
    add_menu_page(__('Unused file checker','wemessage_fix_files'), __('Unused file checker','wemessage_fix_files'), 'administrator', 'wemessage-fix-files', 'wemessage_fix_files_page', plugins_url('/images/icon.png', __FILE__));
}

function wemessage_fix_files_page(){?>
    <div class="wrap">
        <h2><?=__('Check images', 'wemessage_fix_files');?></h2>
        <div class="clear"></div>
        <div id="poststuff">
            <div class="post-body">
                <div class="postbox">
                    <div class="inside">
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">
                                    <span class="button button-primary" onclick="checkDatabase()"><?=__('Check files', 'wemessage_fix_files'); ?></span>
                                </th>
                                <td>
                                    <p class="files"><?=sprintf(__('Found %s files','wemessage_fix_files'), '<span class="amount">0</span>');?></p>
                                    <p class="deleting" style="display:none;"><?=sprintf(__('Deleting files remaining %s from %s', 'wemessage_fix_files'), '<span class="amount">0</span>', '<span class="from"></span>');?></p>
                                    <span class="description"><?=__('This will search for files which are not used in wordpress', 'wemessage_fix_files');?></span>
                                </td>
                            </tr>
                        </table>
                        <div id="dashboard-widgets" class="metabox-holder">
                            <div id="files" class="postbox" style="display:none;">
                                <div class="postbox-header"><h2 class="hndle ui-sortable-handle"><?=__('Files', 'wemessage_fix_files');?></h2></div>
                                <div class="inside">
                                    <div class="progressbar" style="width:100%;height:25px;clear:both; background:#efefef;border:1px inset #efefef">
                                        <div class="progress" style="width:0; height:23px;margin:1px 0;background:#007cba;"></div>
                                    </div>
                                    <div id="deleteFiles" style="display:none;">
                                        <span class="button button-primary" onclick="deleteFiles()"><?=__('Delete all files', 'wemessage_fix_files');?></span>
                                    </div>
                                    <div id="foundfiles" style="display:none;">
                                    
                                    </div>
                                </div>
                            </div>
                        </div>  
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function checkDatabase(){
            var data = {
                'action': 'fc_check_files',
            };
            jQuery.post(ajaxurl, data, function(response) {
                var files = JSON.parse(response);
                //console.log(files);
                jQuery('#files').show();
                processFiles(files.length, files);
            });
        }
        function processFiles(length, files, limit=10){
            var ar = files.splice(0,limit);
            if(files.length){
                jQuery.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {
                        'action': 'fc_process_files',
                        'files': ar
                    },
                    success: function(data){
                        jQuery('#foundfiles').append(data);
                        jQuery('.files .amount').text(jQuery('#foundfiles .notFound').length);
                    }
                }).done(function(data, textStatus, jqXHR){
                    processFiles(length, files);
                    var x = ((length - files.length) * 100) / length;
                    jQuery('#files .progress').width(x+'%');
                });
            } else {
                jQuery('#deleteFiles').show();
                jQuery('#foundfiles').show();
            }
        }
        function deleteRecord(el){
            var data = {
                'action': 'fc_delete_record',
                'id': jQuery(el).closest('.notFound').data(id)
            };
            jQuery.post(ajaxurl, data, function(response) {
                jQuery(el).closest('.notFound').remove();
            });
        }
        function deleteFile(el){
            var data = {
                'action': 'fc_delete_file',
                'file': jQuery(el).closest('.notFound').data(id)
            };
            jQuery.post(ajaxurl, data, function(response) {
                jQuery(el).closest('.notFound').remove();
            });
        }
        
        function deleteFiles(length=0){
        	var ids = Array();
        	if(length == 0){
        		jQuery('.deleting').show();
        		jQuery('.files').hide();
        		jQuery('#files .progress').width('0%');
        		length = jQuery('#foundfiles .notFound').length;
        	}
        	
            jQuery('#foundfiles .notFound.record').each(function(){
                ids.push(jQuery(this).data('id'));
            });
			if(ids.length){
				var ar = ids.splice(0, 10);
				jQuery.ajax({
					type: "POST",
					url: ajaxurl,
					data: {
						'action': 'fc_fc_delete_records',
						'ids': ar
					},
					success: function(data){

					}
				}).done(function(data, textStatus, jqXHR){
					var x = ((length - jQuery('#foundfiles .notFound').length) * 100) / length;
                    jQuery('#files .progress').width(x+'%');
                    jQuery(ar).each(function(i,v){
                    jQuery('#foundfiles .notFound').filter(function(){
                        	return jQuery(this).data('id') === v
                    	}).remove();
                	});
                	jQuery('.deleting .amount').text(jQuery('#foundfiles .notFound').length);
                	jQuery('.deleting .from').text(jQuery('.files .amount').text());
					deleteFiles(length);
				});
			} else {
				jQuery('#foundfiles .notFound.file').each(function(){
					ids.push(jQuery(this).data('id'));
				});
				if(ids.length){
					var ar = ids.splice(0, 10);
					jQuery.ajax({
						type: "POST",
						url: ajaxurl,
						data: {
							'action': 'fc_fc_delete_files',
							'files': ar
						},
						success: function(data){
					
						}
					}).done(function(data, textStatus, jqXHR){
						var x = ((length - jQuery('#foundfiles .notFound').length) * 100) / length;
                    	jQuery('#files .progress').width(x+'%');
                    	jQuery(ar).each(function(i,v){
                    	jQuery('#foundfiles .notFound').filter(function(){
                        		return jQuery(this).data('id') === v
                    		}).remove();
                		});
                		jQuery('.deleting .amount').text(jQuery('#foundfiles .notFound').length);
                		jQuery('.deleting .from').text(jQuery('.files .amount').text());
						deleteFiles(length);
					});
				} else {
					jQuery('#foundfiles').html('');
					jQuery('.deleting').hide();
					jQuery('.files .amount').text(0);
        			jQuery('.files').show();
        			jQuery('#files').hide();
        			jQuery('#files .progress').width('0%');
				}
            }
        }
    </script>
<?php 
}

add_action( 'wp_ajax_fc_check_files', 'fc_check_files' );
function fc_check_files() {
    global $wpdb; 
    $results = [];
    $media = wp_upload_dir();
    $it = new RecursiveDirectoryIterator($media['basedir']);
                
    foreach(new RecursiveIteratorIterator($it) as $file){
    	if($file->getFilename() == '.' || $file->getFilename() == '..') continue;
    	// skip file which matches filename-reolution
    	$fileName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
    	$resolution = substr($fileName, -3);
    	if($resolution == filter_var($resolution, FILTER_VALIDATE_INT)) continue;
    	$results[] = array('path'=>$file->getPathname(), 'name'=>$file->getFilename());
    }
    echo json_encode($results);
    wp_die();
}

add_action( 'wp_ajax_fc_process_files', 'fc_process_files' );
function fc_process_files() {
    global $wpdb;
    foreach($_POST['files'] as $file){
    	$results = $wpdb->get_results('select * from '.$wpdb->postmeta.' where meta_key="_wp_attached_file" and meta_value like "%'.pathinfo($file['name'], PATHINFO_FILENAME).'%"');
    	if(count($results)){
    		foreach($results as $result){
    			if(!$wpdb->get_var('select post_parent from '.$wpdb->posts.' where ID='.$result->post_id)){
    				$res = $wpdb->get_results('select * from '.$wpdb->posts.' where post_content like "%'.pathinfo($file['name'], PATHINFO_FILENAME).'%"');
    				if(!count($res)){
    					$media = wp_upload_dir();
    					echo '<p class="notFound record" style="padding:5px; border:1px solid; background:#fff;width:calc(100% - 12px); display:inline-block;" data-id="'.$result->post_id.'"><img src="'.$media['baseurl'].'/'.$result->meta_value.'" width="40" style="margin-right:10px;" />'.sprintf(__('Image %s was not attached to a post', 'wemessage_fix_files'), '<b>'.$result->meta_value.'</b>').'<span class="button button-secondary pull-right" onclik="deleteRecord(this)">'.__('Delete', 'wemessage_fix_files').'</span></p>';
    				}
    			}
    		}
    	} else {
    		$res = $wpdb->get_results('select * from '.$wpdb->posts.' where post_content like "%'.pathinfo($file['name'], PATHINFO_FILENAME).'%"');
    		if(!count($res)) echo '<p class="notFound file" style="padding:5px; border:1px solid; background:#eee; width:calc(100% - 12px); display:inline-block;" data-id="'.$file['name'].'">'.sprintf(__('File %s was not found in database', 'wemessage_fix_files'),'<b>'.$file['name'].'</b>').'<span class="button button-secondary pull-right" onclik="deleteFile(this)">'.__('Delete', 'wemessage_fix_files').'</p>';
    	}
    }
    wp_die();
}
add_action( 'wp_ajax_fc_fc_delete_records', 'fc_fc_delete_records');
function fc_fc_delete_records() {
    global $wpdb;
    $results = $wpdb->get_results('select * from '.$wpdb->postmeta.' where meta_key="_wp_attached_file" and post_id in ('.implode(',',$_POST['ids']).')');
    foreach($results as $result){
        $media = wp_upload_dir();
        $it = new RecursiveDirectoryIterator($media['basedir']);
        foreach(new RecursiveIteratorIterator($it) as $file){
            if($file->getFilename() == $result->meta_value) {
                unlink($file->getPathname());
            }
            $fileName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
    		$resolution = substr($fileName, -3);
    		if($resolution == filter_var($resolution, FILTER_VALIDATE_INT) && strpos($file->getFilename(), $result->meta_value) !== false) unlink($file->getPathname());
        }
    }
    $wpdb->query('delete from '.$wpdb->postmeta.' where post_id in ('.implode(',',$_POST['ids']).')');
    $wpdb->query('delete from '.$wpdb->posts.' where ID in ('.implode(',',$_POST['ids']).')');
    wp_die();
}
add_action( 'wp_ajax_fc_fc_delete_files', 'fc_fc_delete_files');
function fc_fc_delete_files() {
    foreach($_POST['files'] as $dfile){
        $media = wp_upload_dir();
        $it = new RecursiveDirectoryIterator($media['basedir']);
        foreach(new RecursiveIteratorIterator($it) as $file){
            if($file->getFilename() == $dfile) {
                unlink($file->getPathname());
            }
            $fileName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
    		$resolution = substr($fileName, -3);
    		if($resolution == filter_var($resolution, FILTER_VALIDATE_INT) && strpos($file->getFilename(), $dfile) !== false) unlink($file->getPathname());
        }
    }
    wp_die();
}
add_action( 'wp_ajax_fc_delete_record', 'fc_delete_record');
function fc_delete_record() {
    global $wpdb;
    $results = $wpdb->get_results('select * from '.$wpdb->postmeta.' where meta_key="_wp_attached_file" and post_id='.$_POST['id']);
    foreach($results as $result){
        $media = wp_upload_dir();
        $it = new RecursiveDirectoryIterator($media['basedir']);
                
        foreach(new RecursiveIteratorIterator($it) as $file){
            if($file->getFilename() == $result->meta_value) {
                unlink($file->getPathname());
            }
            $fileName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
    		$resolution = substr($fileName, -3);
    		if($resolution == filter_var($resolution, FILTER_VALIDATE_INT) && strpos($file->getFilename(), $result->meta_value) !== false) unlink($file->getPathname());
        }
    }
    $wpdb->query('delete from '.$wpdb->postmeta.' where post_id='.$_POST['id']);
    $wpdb->query('delete from '.$wpdb->posts.' where ID='.$_POST['id']);
    wp_die();
}
add_action( 'wp_ajax_fc_delete_file', 'fc_delete_file');
function fc_delete_file() {

    $media = wp_upload_dir();
    $it = new RecursiveDirectoryIterator($media['basedir']);
                
    foreach(new RecursiveIteratorIterator($it) as $file){
        if($file->getFilename() == $_POST['file']) {
            unlink($file->getPathname());
        }
        $fileName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
    	$resolution = substr($fileName, -3);
    	if($resolution == filter_var($resolution, FILTER_VALIDATE_INT) && strpos($file->getFilename(), $_POST['file']) !== false) unlink($file->getPathname());
    }
    wp_die();
}