<?php
global $comments_list;
?>

<?php if ( ! empty( $comments_list ) ): ?>
	<?php $i = 0; ?>
	<?php foreach ( $comments_list as $comment ):
		$image_description_num = get_comment_meta( $comment->comment_ID, 'image_description_num', true );
		$status_changed_comment = get_comment_meta( $comment->comment_ID, 'comment_status_change', true );
		$status_class = $status_changed_comment ? ' status_changed ' : '';
		$status_author = $status_changed_comment ? $comment->comment_author : '';
		$comment_has_parent = get_comment_meta( $comment->comment_ID, 'parent_description_num', true );
		if ( $comment_has_parent ) {
			continue;
		}
		?>
        <div class="comment-wrap <?php echo $i % 2 == 0 ? 'odd' : 'even';
		echo $status_class; ?>">
            <div class="grid">
                <div class="author-avatar">
					<?php echo get_avatar( $comment, 48 ); ?>
                </div>
                <div class="author-name">
					<?php echo $comment->comment_author ?>
                </div>
				<?php if ( $status_class ) : ?>
                    <div class="content">
						<?php echo '<b>' . $status_author . '</b> ' . $this->makeLinks( $comment->comment_content ); ?>
                    </div>
				<?php endif; ?>
                <div class="date">
					<?php echo date( 'm/d/Y @ g:i a', strtotime( $comment->comment_date ) ); ?>
                </div>
            </div>
			<?php
			
			if ( ! $status_class ) : ?>
                <div class="content">
					<?php echo '<b>' . $status_author . '</b> ' . $comment->comment_content ?>
					<?php if ( $image_description_num ): ?>
                        <div class="task_circle_num" data-value="<?php echo $image_description_num; ?>">
							<?php echo $image_description_num; ?>
                        </div>
					<?php endif; ?>
                </div>
				<?php
				if ( $image_description_num ) :?>
                    <div class="comment_thread">
                        <a href="#" class="comment_reply" data-comment-id="<?php echo $comment->comment_ID ?>"
                           data-post-id="<?php echo $comment->comment_post_ID ?>">Reply</a>
                        <div class="thread_wrp">
							<?php foreach ( $comments_list as $sub_comment ):
								$comment_has_parent = get_comment_meta( $sub_comment->comment_ID, 'parent_description_num', true );
								if ( $image_description_num == $comment_has_parent ) :
									
									$status_changed_comment_reply = get_comment_meta( $sub_comment->comment_ID, 'comment_status_change', true );
									$status_class_reply = $status_changed_comment_reply ? ' status_changed ' : '';
									$status_author_reply = $status_changed_comment_reply ? $sub_comment->comment_author : '';
									$comment_client_author_reply = get_comment_meta( $sub_comment->comment_ID, 'comment_client_author', true );
									$comment_client_image_reply = get_comment_meta( $sub_comment->comment_ID, 'comment_client_image', true );
									$user_tag_html_reply = '';
									if ( $comment_has_parent ) :
										if ( $comment_has_parent ) : ?>
                                            <div class="comment-wrap <?php echo $i % 2 == 0 ? 'odd' : 'even';
											echo $status_class; ?>">
                                                <div class="grid">
                                                    <div class="author-avatar">
														<?php echo get_avatar( $sub_comment, 48 ); ?>
                                                    </div>
                                                    <div class="author-name">
														<?php echo $sub_comment->comment_author ?>
                                                    </div>
													<?php if ( $status_class ) : ?>
                                                        <div class="content">
															<?php echo '<b>' . $status_author . '</b> ' . $this->makeLinks( $sub_comment->comment_content ); ?>
                                                        </div>
													<?php endif; ?>
                                                    <div class="date">
														<?php echo date( 'm/d/Y @ g:i a', strtotime( $sub_comment->comment_date ) ); ?>
                                                    </div>
                                                </div>
												<?php if ( ! $status_class ) : ?>
                                                    <div class="content">
														<?php echo '<b>' . $status_author . '</b> ' . $this->makeLinks( $sub_comment->comment_content ); ?>
                                                    </div>
												<?php endif; ?>
                                            </div>
										<?php endif;
									endif;
								endif;
							endforeach; ?>
                            <form class="thread_comment_submit_box">
                                <div class="ns-error-box"></div>
                                <input type="hidden" name="post_id" value="<?php echo $comment->comment_post_ID ?>">
                                <input type="hidden" name="client_id" value="<?php echo $client_id ?>">
								<?php if ( $image_description_num ): ?>
                                    <input type="hidden" name="parent_description_num"
                                           value="<?php echo $image_description_num; ?>">
								<?php endif; ?>
                                <textarea name="task_thread_textarea" class="task_thread_textarea" rows="4"></textarea>
                                <div class="button-wrap">
                                    <button type="submit" class="thread_task_button_submit ns_green_button">Comment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
				<?php endif; ?>
			<?php endif; ?>

        </div>
		<?php $i ++; ?>
	<?php endforeach; ?>
<?php endif; ?>
