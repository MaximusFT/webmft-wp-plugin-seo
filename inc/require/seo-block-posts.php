<?php
	if (!empty($this->options['extposts_featured_slot_machines']) ) {
		  			$text_1 = '<h2>'.$this->options['extposts_nazanie_h2_before_featured'].'</h2>';
		  			$text_1_1 ='<h2>'.$this->options['extposts_nazanie_h2_after_featured'].'</h2>';
		  			$number_blok_o = $this->options['extposts_kolichestvo_postov'];
		  			$number_id_cat_one_o = $this->options['extposts_id_postov_one'];
		  			$tmp_text = WEBMFT_PostMostViewed_Widget::widget(['format'=>1], ['num'=>$number_blok_o, 'echo'=>0, 'cat'=>$number_id_cat_one_o]);
				}
					if (!empty($this->options['extposts_casino_reviews']) ) {
		  			$text_1_2 = '<h2>'.$this->options['extposts_nazanie_h2_before_casino_reviews'].'</h2>';
		  			$text_1_1_2 ='<h2>'.$this->options['extposts_nazanie_h2_after_casino_reviews'].'</h2>';
		  			$number_blok_one = $this->options['extposts_kolichestvo_postov_casino_reviews'];
		  			$number_id_cat_one_one = $this->options['extposts_id_postov_one_one'];
		  			$rec_copy_text = WEBMFT_PostMostViewed_Widget::widget(['format'=>2], ['num'=>$number_blok_one, 'echo'=>1, 'cat'=>$number_id_cat_one_one]);
				}
		  		if (!empty($this->options['extposts_all_slots']) ) {
		  			$text_2 = '<h2>'.$this->options['extposts_nazanie_h2_all_slots'].'</h2>';
		  			$text_2_1 ='<h2>'.$this->options['extposts_nazanie_h2_all_slots_after'].'</h2>';
		  			$number_blok_two = $this->options['extposts_kolichestvo_postov_two_blok'];
		  			$number_id_cat_two = $this->options["extposts_id_categories_2_blok"];
		  			$rec_text = WEBMFT_PostNext_Widget::kama_recent_posts($number_blok_two, '', $number_id_cat_two);
		  		}
		  		if (!empty($this->options['extposts_gamblink_news']) ) {
		  			$text_3 = '<h2>'.$this->options['extposts_nazanie_h2_gamblink_news'].'</h2>';
		  			$text_3_1 ='<h2>'.$this->options['extposts_nazanie_h2_gamblink_news_after'].'</h2>';
		  			$number_blok_three = $this->options['extposts_kolichestvo_postov_news_blok'];
		  			$number_id_cat_three = $this->options['extposts_id_categories_3_blok'];
		  			$recs_text = WEBMFT_PostNext_Widget::kama_recent_posts($number_blok_three, '', $number_id_cat_three);
		  		}		  	
?>