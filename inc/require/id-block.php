<?php
if ($this->options['goto_setup_link'] == ''){
	$this->options['goto_setup_link'] = 'goto';
	$goto_setup_link = 'goto';
	$goto_setup_link = '/'.$goto_setup_link.'/1/';
} else {
	$goto_setup_link = $this->options['goto_setup_link'];
	$goto_setup_link = '/'.$goto_setup_link.'/1/';
}
		if (!empty($this->options['extposts_ids_posts_one_style']) ) {
				$id_post = $this->options['extposts_id_nyhnovo_posta_ones'];//айди нужно поста
				$id_id_post = explode(",", $id_post);
				foreach ($id_id_post as $id_post) {
					$post_id = get_post( $id_post, ARRAY_A);
					$title = $post_id['post_title']; //название поста
					$conte = $post_id['post_content'];//берем текст поста
					$conte = strip_tags($conte);//удаляем html теги
					if ('' == $this->options['kolichestvo_front']){$length = 30;}
					else {$length = $this->options['kolichestvo_front'];}
					$postfix='...';
				    if ( strlen($conte) <= $length){
				        return $conte;
				    }
				    $temp = substr($conte, 0, $length);
				    $contez =  substr($temp, 0, strrpos($temp, ' ') ) . $postfix;
					if ('' == $this->options['kolichestvo_back']){$length_two = 100;}
					else {$length_two = $this->options['kolichestvo_back'];}
					$postfix='...';
				    if ( strlen($conte) <= $length_two){
				        return $conte;
				    }
				    $temps = substr($conte, 0, $length_two);
				    $contezq =  substr($temps, 0, strrpos($temps, ' ') ) . $postfix;
					$numbersl = get_post_meta ($id_post,'views',true);
    				$numral = $numbersl / 100 * 100;
    				$numretigl = $numral /10;
					if ( $numretigl >= 10 ){
						$numretigl = 10;
					} else {
						$numretigl;
					}
					$thumb = get_the_post_thumbnail( $id_post, array(90,90) ); //миниатюра
					$url = get_permalink($id_post); //урл поста
					if ('' == $this->options['zamena_visit']){$Visit = 'Visit';}
					else {$Visit = $this->options['zamena_visit'];}
					if ('' == $this->options['zamena_certified']){$Certified = 'Certified';}
					else {$Certified = $this->options['zamena_certified'];}
					if ('' == $this->options['zamena_play_now']){$play_now = 'Play now';}
					else {$play_now = $this->options['zamena_play_now'];}
					$echosl .= '<li class="card-list__item"><div class="card"><div class="card__front"><span class="card__open-info do-open-info"><i class="fa fa-toggle-on" aria-hidden="true"></i></span><div class="card__media card__media--rating card__media--casino"><a href="'.$url.'">'.$thumb.'</a><div 
						class="card__ratingq ratingq"><div class="starq-ratingq"><span style="width: '.$numral.'%;"></span></div><span class="starq-ratingq--after">'.$numretigl.'</span></div></div><div class="card__desc"><h3 class="card__desc-title card__desc-title--clip"><span class="title">'.$title.'</span></h3><p class="card__desc-body">'.$contez.'<p class="card__status card__status--with-action"><i class="fa fa-check-square-o" aria-hidden="true"></i>'.$Certified.'</p></div><div class="card__action"><a class="visti" href="'.$goto_setup_link.'" >'.$Visit.'</a></div></div><div class="card__back"><span class="card__close-info do-close-info"><i class="fa fa-toggle-off" aria-hidden="true"></i></span><p class="card__desc-title card__desc-title--clip">'.$title.'</p><p class="card__desc-body">'.$contezq.'</p><a target="_blank" href="'.$goto_setup_link.'"  class="btnssabtns">'.$play_now.'</a></div></div></li>';
				}//foreach   
		}
		if (!empty($this->options['extposts_ids_posts_two_style']) ) {
				$id_post = $this->options['id_nyhnovo_posta_style_2'];//айди нужно поста
				$id_id_post = explode(",", $id_post);
				foreach ($id_id_post as $id_post) {
					$post_id = get_post( $id_post, ARRAY_A);
					$title = $post_id['post_title']; //название поста
					$thumb = get_the_post_thumbnail( $id_post, array(90,90) ); //миниатюра
					$url = get_permalink($id_post); //урл поста
					if ('' == $this->options['zamena_play_block_2']){$play = 'Play';}
					else {$play = $this->options['zamena_play_block_2'];}
					if ('' == $this->options['zamena_review_block_2']){$review = 'Review';}
					else {$review = $this->options['zamena_review_block_2'];}
					$echosl_1 .= '<li class="games-list__item"><div class="game-box "><span class="imageloader loading game-box__img-holder">'.$thumb.'</span><div class="game-box__action-content"><div class="game-box__align-content"><h3 class="game-box__title">'.$title.'</h3><div class="game-box__holder"><a class="button_button_size_m" href="'.$goto_setup_link.'">'.$play.'</a></div><div class="game-box__demo-holder"><a class="game-box__pseudo-link" href="'.$url.'">'.$review.'</a></div></div></div></div></li>';
				}//foreach
		}
		if (!empty($this->options['extposts_ids_posts_three_style']) ) {
				$id_post = $this->options['id_nyhnovo_posta_style_3'];//айди нужно поста
				$id_id_post = explode(",", $id_post);
				foreach ($id_id_post as $id_post) {
					$post_id = get_post( $id_post, ARRAY_A);
					$title = $post_id['post_title']; //название поста
					$thumb = get_the_post_thumbnail( $id_post, array(90,90) ); //миниатюра
					$url = get_permalink($id_post); //урл поста
					$echosl_2 .= '<div class="casino-box"> <div class="thumbnail"> <a href="'.$url.'"> '.$thumb.'</a><form><button formaction="'.$goto_setup_link.'" class="btn-tocasino btn-orange">'.$title.'</button></form></div></div>';
				}//foreach
		}
		if (!empty($this->options['extposts_ids_posts_four_style']) ) {
				$id_post = $this->options['id_nyhnovo_posta_style_4'];//айди нужно поста
				$id_id_post = explode(",", $id_post);
				foreach ($id_id_post as $id_post) {
					$post_id = get_post( $id_post, ARRAY_A);
					$title = $post_id['post_title']; //название поста
					$url = get_permalink($id_post); //урл поста
					$thumb = get_the_post_thumbnail( $id_post, array(150,150) );
					if ('' == $this->options['zamena_review_block_4']){$review_style_4 = 'Review';}
					else {$review_style_4 = $this->options['zamena_review_block_4'];}
					$echosl_3 .= '<div class="mrg-slots-cards"><a href="'.$url.'"><div class="mrg-slot-card-img lazy"><p class="pabz">'.$thumb.'</p><div class="mrg-slot-info"><h1 class="myh1">'.$title.'</h1></div></div></a><div class="mrg-slot-play-btn"><a href="'.$url.'">'.$review_style_4.'</a></div></div>';
				}//foreach
		}
		if (!empty($this->options['extposts_ids_posts_five_style']) ) {
				$id_post = $this->options['id_nyhnovo_posta_style_5'];//айди нужно поста
				$id_id_post = explode(",", $id_post);
				foreach ($id_id_post as $id_post) {
					$post_id = get_post( $id_post, ARRAY_A);
					$title = $post_id['post_title']; //название поста
					$url = get_permalink($id_post); //урл поста
					$thumb = get_the_post_thumbnail( $id_post, array(130,130) );
					$echosl_4 .= '<div class="ben_ben_1"><a class="class_imfs" href="'.$url.'">'.$thumb.'</a><a class="button_k_ben" href="'.$url.'" style="text-decoration: none; line-height: 35px;"><p class="p_ben_p">'.$title.'</p></a></div>';
				}//foreach
    	}
		if (!empty($this->options['extposts_ids_posts_six_style']) ) {
				$id_post = $this->options['id_nyhnovo_posta_style_6'];//айди нужно поста
				$id_id_post = explode(",", $id_post);
				foreach ($id_id_post as $id_post) {
					$post_id = get_post( $id_post, ARRAY_A);
					$title = $post_id['post_title']; //название поста
					$url = get_permalink($id_post); //урл поста
					$thumb = get_the_post_thumbnail( $id_post, array(210,210) );
					$echosl_5 .= '<li><figure>'.$thumb.'<figcaption><a href="'.$url.'">'.$title.'</a></figcaption></figure></li>';
				}//foreach
    	}
		if (!empty($this->options['extposts_ids_posts_seven_style']) ) {
				$id_post = $this->options['id_nyhnovo_posta_style_7'];//айди нужно поста
				$id_id_post = explode(",", $id_post);
				foreach ($id_id_post as $id_post) {
					$post_id = get_post( $id_post, ARRAY_A);
					$title = $post_id['post_title']; //название поста
					$conte = $post_id['post_content'];//берем текст поста
					$conte = strip_tags($conte);//удаляем html теги
					if ('' == $this->options['kolichestvo_simvolo']){$length_twoq = 50;}
					else {$length_twoq = $this->options['kolichestvo_simvolo'];}
					$postfix='...';
				    if ( strlen($conte) <= $length_twoq){
				        return $conte;
				    }
				    $temps = substr($conte, 0, $length_twoq);
				    $contezq =  substr($temps, 0, strrpos($temps, ' ') ) . $postfix;
					$numbersl = get_post_meta ($id_post,'views',true);
    				$numral = $numbersl / 100 * 100;
    				$numretigl = $numral /10;
					if ( $numretigl >= 10 ){
						$numretigl = 10;
					} else {
						$numretigl;
					}
					$thumb = get_the_post_thumbnail( $id_post, array(130,130) ); //миниатюра
					$url = get_permalink($id_post);
					if ('' == $this->options['zamena_review_style_6']){$Review_style_6 = 'Review';}
					else {$Review_style_6 = $this->options['zamena_review_style_6'];}
					$echosl_6 .= '<tbody> <tr> <td class="z-1">'.$thumb.'</td> <td class="z-2"><div class="card__ratingzq ratingzq"><div class="star-ratingzq"><span style="width: '. $numral.'%;"></span></div></div>'.$contezq.'<br><p class="url_link_title"><a href="'.$url.'">'.$title.' '.$Review_style_6.'</a></p></td> <td class="z-3"><button class="button_my_my" formaction="'.$goto_setup_link.'">'.$title.'</button></td> </tr> </tbody>';
				}//foreach
    	}



                



				
				if (!empty($this->options['extposts_ids_posts']) ) {
					if (!empty($this->options['extposts_ids_posts_one_style']) ) {
						$echosl = '<ul class="card-list group">'.$echosl.'</ul>';
					}
					if (!empty($this->options['extposts_ids_posts_two_style']) ) {
						$echosl_1 = '<ul class="games-list">'.$echosl_1.'</ul>';
					}
					if (!empty($this->options['extposts_ids_posts_three_style']) ) {
						$echosl_2 = '<div class="loop-container cf">'.$echosl_2.'</div>';
					}
					if (!empty($this->options['extposts_ids_posts_four_style']) ) {
						$echosl_3 = '<div class="post-most-viewed">'.$echosl_3.'</div>';
					}
					if (!empty($this->options['extposts_ids_posts_four_style']) ) {
						$echosl_4 = '<div class="static">'.$echosl_4.'</div>';
					}
					if (!empty($this->options['extposts_ids_posts_six_style']) ) {
						$echosl_5 = '<ul class="gridq cs-style-7">'.$echosl_5.'</ul>';
					}
					if (!empty($this->options['extposts_ids_posts_seven_style']) ) {
						$echosl_6 = '<table class="table_list">'.$echosl_6.'</table>';
					}
					$text = $text.$echosl.'<br>'.$echosl_1.'<br>'.$echosl_2.'<br>'.$echosl_3.'<br>'.$echosl_4.'<br>'.$echosl_5.'<br>'.$echosl_6;
					}
?>