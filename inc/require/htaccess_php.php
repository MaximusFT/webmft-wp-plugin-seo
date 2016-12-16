<?php

if (!defined('ABSPATH')) die('Первый файл первая строка');


if(!is_admin()){
   return;
}else{
/*
if(!function_exists('wp_get_current_user')){
	if(file_exists(ABSPATH.'wp-includes/pluggable.php')){
		require_once ABSPATH.'wp-includes/pluggable.php';
	}else{
		wp_die(__('Plugin WP Htaccess Editor Error: File "/wp-includes/pluggable.php" does not exists!', 'wphe'));
	}
}
if(!function_exists('current_user_can')){
	if(file_exists(ABSPATH.'wp-includes/capabilities.php')){
		require_once ABSPATH.'wp-includes/capabilities.php';
	}else{
		wp_die(__('Plugin WP Htaccess Editor Error: File "/wp-includes/capabilities.php" does not exists!', 'wphe'));
	}
}*/



/*
function WPHE_CreateBackup(){

	$WPHE_backup_path = ABSPATH.'wp-content/htaccess.backup';
	$WPHE_orig_path = ABSPATH.'.htaccess';
	@clearstatcache();
	WPHE_CreateSecureWPcontent();
	if(file_exists($WPHE_backup_path)){
		WPHE_DeleteBackup();

		if(file_exists(ABSPATH.'.htaccess')){
			$htaccess_content_orig = @file_get_contents($WPHE_orig_path, false, NULL);
			$htaccess_content_orig = trim($htaccess_content_orig);
			$htaccess_content_orig = str_replace('\\\\', '\\', $htaccess_content_orig);
			$htaccess_content_orig = str_replace('\"', '"', $htaccess_content_orig);
			@chmod($WPHE_backup_path, 0666);
			$WPHE_success = @file_put_contents($WPHE_backup_path, $htaccess_content_orig, LOCK_EX);
			if($WPHE_success === false)
			{
				unset($WPHE_backup_path);
				unset($WPHE_orig_path);
				unset($htaccess_content_orig);
				unset($WPHE_success);
				return false;
			}else{
				unset($WPHE_backup_path);
				unset($WPHE_orig_path);
				unset($htaccess_content_orig);
				unset($WPHE_success);
				return true;
			}
			@chmod($WPHE_backup_path, 0644);
		}else{
			unset($WPHE_backup_path);
			unset($WPHE_orig_path);
			return false;

		}
	}else{
		if(file_exists(ABSPATH.'.htaccess')){
			$htaccess_content_orig = @file_get_contents($WPHE_orig_path, false, NULL);
			$htaccess_content_orig = trim($htaccess_content_orig);
			$htaccess_content_orig = str_replace('\\\\', '\\', $htaccess_content_orig);
			$htaccess_content_orig = str_replace('\"', '"', $htaccess_content_orig);
			@chmod($WPHE_backup_path, 0666);
			$WPHE_success = @file_put_contents($WPHE_backup_path, $htaccess_content_orig, LOCK_EX);
			if($WPHE_success === false){
				unset($WPHE_backup_path);
				unset($WPHE_orig_path);
				unset($htaccess_content_orig);
				unset($WPHE_success);
				return false;
			}else{
				unset($WPHE_backup_path);
				unset($WPHE_orig_path);
				unset($htaccess_content_orig);
				unset($WPHE_success);
				return true;
			}
			@chmod($WPHE_backup_path, 0644);
		}else{
			unset($WPHE_backup_path);
			unset($WPHE_orig_path);
			return false;
		}
	}
}*/
/*
function WPHE_CreateSecureWPcontent(){
	$wphe_secure_path = ABSPATH.'wp-content/.htaccess';
	$wphe_secure_text = '
# WP Htaccess Editor - Secure backups
<files htaccess.backup>
order allow,deny
deny from all
</files>
';
	if(is_readable(ABSPATH.'wp-content/.htaccess')){
		$wphe_secure_content = @file_get_contents(ABSPATH.'wp-content/.htaccess');
		if($wphe_secure_content !== false){
			if(strpos($wphe_secure_content, 'Secure backups') === false){
				unset($wphe_secure_content);
				$wphe_create_sec = @file_put_contents(ABSPATH.'wp-content/.htaccess', $wphe_secure_text, FILE_APPEND|LOCK_EX);
				if($wphe_create_sec !== false){
					unset($wphe_secure_text);
					unset($wphe_create_sec);
					return true;
				}else{
					unset($wphe_secure_text);
					unset($wphe_create_sec);
					return false;
				}
			}else{
				unset($wphe_secure_content);
				return true;
			}
		}else{
			unset($wphe_secure_content);
			return false;
		}
	}else{
		if(file_exists(ABSPATH.'wp-content/.htaccess')){
			return false;
		}else{
			$wphe_create_sec = @file_put_contents(ABSPATH.'wp-content/.htaccess', $wphe_secure_text, LOCK_EX);
			if($wphe_create_sec !== false){
				return true;
			}else{
				return false;
			}
		}
	}
}*/
/*
function WPHE_RestoreBackup(){
	$wphe_backup_path = ABSPATH.'wp-content/htaccess.backup';
	$WPHE_orig_path = ABSPATH.'.htaccess';
	@clearstatcache();

	if(!file_exists($wphe_backup_path)){
		unset($wphe_backup_path);
		unset($WPHE_orig_path);
		return false;
	}else{
		if(file_exists($WPHE_orig_path)){
			if(is_writable($WPHE_orig_path)){
				@unlink($WPHE_orig_path);
			}else{
				@chmod($WPHE_orig_path, 0666);
				@unlink($WPHE_orig_path);
			}
		}
		$wphe_htaccess_content_backup = @file_get_contents($wphe_backup_path, false, NULL);
		if(WPHE_WriteNewHtaccess($wphe_htaccess_content_backup) === false){
			unset($wphe_success);
			unset($WPHE_orig_path);
			unset($wphe_backup_path);
			return $wphe_htaccess_content_backup;
		}else{
			WPHE_DeleteBackup();
			unset($wphe_success);
			unset($wphe_htaccess_content_backup);
			unset($WPHE_orig_path);
			unset($wphe_backup_path);
			return true;
		}
	}
}*/

/*
function WPHE_DeleteBackup(){
	$wphe_backup_path = ABSPATH.'wp-content/htaccess.backup';
	@clearstatcache();

	if(file_exists($wphe_backup_path)){
		if(is_writable($wphe_backup_path)){
			@unlink($wphe_backup_path);
		}else{
			@chmod($wphe_backup_path, 0666);
			@unlink($wphe_backup_path);
		}

		@clearstatcache();

		if(file_exists($wphe_backup_path)){
			unset($wphe_backup_path);
			return false;
		}else{
			unset($wphe_backup_path);
			return true;
		}
	}else{
		unset($wphe_backup_path);
		return true;
	}
}*/




function WPHE_WriteNewHtaccess($WPHE_new_content){

	$WPHE_orig_path = ABSPATH.'.htaccess';
	@clearstatcache();

	if(file_exists($WPHE_orig_path)){
		if(is_writable($WPHE_orig_path))	{
			@unlink($WPHE_orig_path);
		}else{
			@chmod($WPHE_orig_path, 0666);
			@unlink($WPHE_orig_path);
		}
	}
	$WPHE_new_content = trim($WPHE_new_content);
	$WPHE_new_content = str_replace('\\\\', '\\', $WPHE_new_content);
	$WPHE_new_content = str_replace('\"', '"', $WPHE_new_content);
	$WPHE_write_success = @file_put_contents($WPHE_orig_path, $WPHE_new_content, LOCK_EX);
	@clearstatcache();
	if(!file_exists($WPHE_orig_path) && $WPHE_write_success === false){
		unset($WPHE_orig_path);
		unset($WPHE_new_content);
		unset($WPHE_write_success);
		return false;
	}else{
		unset($WPHE_orig_path);
		unset($WPHE_new_content);
		unset($WPHE_write_success);
		return true;
	}
}
/*
function WPHE_Debug($data){
	echo '<pre>';
	var_dump($data);
	echo '</pre>';
}*/






	$WPHE_orig_path = ABSPATH.'.htaccess';
	?>
	<div class="wrap">
	<h2 class="wphe-titles">Htaccess Editor WebMFT: SEO useful</h2>
	<?php
	
	if(!empty($_POST['submit']) AND !empty($_POST['submit_htaccess']) AND check_admin_referer('wphe_save', 'wphe_save')){
		
		
		$WPHE_new_content = $_POST['ht_content'];
		//WPHE_DeleteBackup();
		/*if(WPHE_CreateBackup()){*/
			if(WPHE_WriteNewHtaccess($WPHE_new_content)){
				echo'<div id="message" class="updated fade"><p><strong>'.__('File has been successfully changed', 'wphe').'</strong></p></div>';
				?>
				<p><?php _e('You have made changes to the htaccess file. The original file was automatically backed up (in <code>wp-content</code> folder)', 'wphe'); ?><br 
				<a href="<?php echo get_option('home'); ?>/" target="_blank"><?php _e('Check the functionality of your site (the links to the articles or categories).', 'wphe');?></a>. <?php _e('If something is not working properly restore the original file from backup', 'wphe');?></p>
				<div class="postbox" style="float: left; width: 95%; padding: 15px;">
				<form method="post">
				<?php wp_nonce_field('wphe_delete','wphe_delete'); ?>
				<input type="hidden" name="delete_backup" value="delete" />
				<p class="submit"><?php _e('If everything works properly, you can delete the backup file:', 'wphe');?> <input type="submit" class="button button-primary" name="submit" value="<?php _e('Remove backup &raquo;', 'wphe');?>" />&nbsp;<?php echo __('or','wphe'); ?>&nbsp;<?php _e('restore the original file from backup','wphe');?></p>
				</form> 
				</div>
				<?php 
			}else{
				echo'<div id="message" class="error fade"><p><strong>'.__('The file could not be saved!', 'wphe').'</strong></p></div>';
				echo'<div id="message" class="error fade"><p><strong>'.__('Due to server configuration can not change permissions on files or create new files','wphe').'</strong></p></div>';
			}
		/*}else{
			echo'<div id="message" class="error fade"><p><strong>'.__('The file could not be saved!', 'wphe').'</strong></p></div>';
			echo'<div id="message" class="error fade"><p><strong>'.__('Unable to create backup of the original file! <code>wp-content</code> folder is not writeable! Change the permissions this folder!', 'wphe').'</strong></p></div>';
		}*/
		unset($WPHE_new_content);
	//============================ Vytvoření nového Htaccess souboru ================================
	}elseif(!empty($_POST['submit']) AND !empty($_POST['create_htaccess']) AND check_admin_referer('wphe_create', 'wphe_create')){
		if(WPHE_WriteNewHtaccess('# Created by WP Htaccess Editor') === false)
		{
			echo'<div id="message" class="error fade"><p><strong>'.__('Htaccess file is not created.', 'wphe').'</p></div>';
			echo'<div id="message" class="error fade"><p><strong>'.__('Due to server configuration can not change permissions on files or create new files','wphe').'</strong></p></div>';
        }else{
			echo'<div id="message" class="updated fade"><p><strong>'.__('Htaccess file was successfully created.', 'wphe').'</strong></p></div>';
			echo'<div id="message" class="updated fade"><p><strong>'.__('View new Htaccess file', 'wphe').'</strong></p></div>';
        }
	//============================ Smazání zálohy =======================================
	}elseif(!empty($_POST['submit']) AND !empty($_POST['delete_backup']) AND check_admin_referer('wphe_delete', 'wphe_delete'))	{
        if(WPHE_DeleteBackup() === false)
		{
           echo'<div id="message" class="error fade"><p><strong>'.__('Backup file could not be removed! <code>wp-content</code> folder is not writeable! Change the permissions this folder!', 'wphe').'</p></div>';
        }else{
           echo'<div id="message" class="updated fade"><p><strong>'.__('Backup file has been successfully removed.', 'wphe').'</strong></p></div>';
        }
	//============================ Home ================================================
	}else{
		?>
		<p><?php _e('Using this editor you can easily modify your htaccess file without having to use an FTP client.', 'wphe');?></p>
		<p class="wphe-red"><?php _e('<strong>WARNING:</strong> Any error in this file may cause malfunction of your site!', 'wphe');?><br />
		<?php _e('Edit htaccess file should therefore be performed only by experienced users!', 'wphe');?><br />
		</p>
		<?php
		if(!file_exists($WPHE_orig_path))
		{
			echo'<div class="postbox wphe-box">';
			echo'<pre class="wphe-red">'.__('Htaccess file does not exists!', 'wphe').'</pre>';
			echo'</div>';
			$success = false;
		}else{
			$success = true;
			if(!is_readable($WPHE_orig_path))
			{
				echo'<div class="postbox wphe-box">';
				echo'<pre class="wphe-red">'.__('Htaccess file cannot read!', 'wphe').'</pre>';
				echo'</div>';
				$success = false;
			}
			if($success == true){
				@chmod($WPHE_orig_path, 0644);
				$WPHE_htaccess_content = @file_get_contents($WPHE_orig_path, false, NULL);
				if($WPHE_htaccess_content === false){
					echo'<div class="postbox wphe-box">';
					echo'<pre class="wphe-red">'.__('Htaccess file cannot read!', 'wphe').'</pre>';
					echo'</div>';
					$success = false;
				}else{
					$success = true;
				}
			}
		}

		if($success == true){
			?>
			<div class="postbox wphe-box">
			<form method="post" action="">
			<?php wp_nonce_field('wphe_save','wphe_save'); ?>
			<h3 class="wphe-title"><?php _e('Content of the Htaccess file', 'wphe');?></h3>
			<textarea name="ht_content" class="wphe-textarea" wrap="off"><?php echo $WPHE_htaccess_content;?></textarea>
			 <p><input type="submit" class="button hide-if-no-js" name="submit_htaccess" id="submit_htaccess" value="Generate Thumbnails" /></p>
			</form>
			</div>
			<?php
			unset($WPHE_htaccess_content);
		}else{
			?>
			<div class="postbox wphe-box" style="background: #E0FCE1;">
			<form method="post" action="">
			<input type="hidden" name="create_htaccess" value="create" />
			<?php wp_nonce_field('wphe_create','wphe_create'); ?>
			</form>
			</div>
			<?php
		}
		unset($success);
	}
	?>
	</div>
	<?php
	unset($WPHE_orig_path); } ?>
