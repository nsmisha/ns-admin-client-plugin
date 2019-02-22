<?php
global $tasks_list;
$page_url = admin_url( 'admin.php?page=ns_plugin_task_edit_page' );
$tasks_order = get_site_option( 'tasks_order_option' );
//print_r($tasks_order);
?>
<?php if ( ! empty( $tasks_list['posts'] ) ): ?>
	<?php foreach ( $tasks_list['posts'] as $k => $task ): ?>
        <div class="grid ui-state-default task-item" data-ns-order="<?php echo $task->menu_order ?: $k; ?>"
             data-ns-id="<?php echo $task->ID; ?>">
            <a href="<?php //echo $page_url . '&edit=' . $task->ID ?>"
               data-remodal-target="task-view-display_<?php echo $task->ID; ?>">
                <div class="identity col-2-12 text-right"><?php echo $this->task_settings->check_default_status( $task->post_status )
						? '<span class="state-pending">' . ( $this->task_settings->exclude_prefix( $task->post_status ) ) . '</span>'
						: get_post_meta( $task->ID, '_task_id_hub', true ); ?></div>
                <div class="title col-5-12">
					<?php echo $task->post_title; ?>
                </div>
                <div class="additional-info col-5-12 handle text-right">
					<?php if ( in_array( $task->post_status, $this->task_settings->get_client_in_progress() ) || in_array( $task->post_status, array_merge( $this->task_settings->get_approved_on_staging_statuses(), $this->task_settings->get_approved_live_statuses() ) ) ) : ?>
						<?php
						if ( $this->task_settings->is_aproved_on_live( $task->post_status ) ) :
							echo '<span class="post_date">' . get_the_date( "m.d.y", $task->ID ) . '</span>';
						else : ?>
                            <div href="<?php //echo $page_url . '&edit=' . $task->ID ?>"
                                 class="ns-burger-btn <?php echo $this->task_settings->check_default_status( $task->post_status )
								     ? 'orange'
								     : ''; ?>">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
						<?php endif; ?>
					<?php else: ?>
						<?php //$state = $this->task_settings->get_state($task->post_status);
						if ( $this->task_settings->is_closed( $task->post_status ) ) :
							$state = 'closed'; ?>
							<?php echo '<span class="post_date">' . get_the_modified_date( "m.d.y", $task->ID ) . '</span>'; ?>
						<?php endif;
						?>
                        <span class="state-<?php echo $state; ?>"><?php echo $state; ?></span>
					<?php endif; ?>
                </div>
            </a>
        </div>
		<?php
		$color_class = '';
		if ( in_array( $task->post_status, $this->task_settings->get_client_in_progress() ) ) {
			$color_class = 'request-task';
		} else if ( in_array( $task->post_status, $this->task_settings->get_approved_live_statuses() ) ) {
			$color_class = 'live-task';
		} else if ( in_array( $task->post_status, $this->task_settings->get_closed_statuses() ) ) {
			$color_class = 'closed-task';
		}
		$closed_class = '';
		if ( $this->task_settings->is_closed( $task->post_status ) ) {
			$closed_class = 'closed_task';
		}
		?>
        <section class="new-task-input-container view_task_container remodal <?php echo $color_class; ?>"
                 data-task-id="<?php echo $task->ID; ?>"
                 data-task-hub-id="<?php echo get_post_meta( $task->ID, '_task_id_hub', true ); ?>"
                 data-remodal-id="task-view-display_<?php echo $task->ID; ?>"
                 data-remodal-options="hashTracking: true, closeOnOutsideClick: true">
            <div>
                <div class="remodal-close <?php echo $closed_class; ?>">
                    <span data-remodal-action="close" class="back dashicons-arrow-left-alt"></span>
                    <div class="task_title_wrp_to_crop">
                        <span><?php echo $this->task_settings->check_default_status( $task->post_status )
		                        ? '<span class="state-pending">[' . $this->task_settings->exclude_prefix( $task->post_status ) . ']</span><span class="task_header_title"> ' . $task->post_title . '</span>'
		                        : '<span class="task_header_title"><span>#' . get_post_meta( $task->ID, '_task_id_hub', true ) . '</span> ' . $task->post_title; ?>
                        </span>
                    </div>
                    <div class="prev_next_task">
						<?php
						$statuses = $this->task_settings->get_statuses();
						$prev     = [];
						$next     = [];
						if ( ! empty ( $tasks_list['posts'] ) ) {
							foreach ( $tasks_list['posts'] as $k => $value ) {
								if ( $value->ID == $task->ID ) {
									//now lets find rpevious task
									$prev = isset( $tasks_list['posts'][ $k - 1 ] ) ? $tasks_list['posts'][ $k - 1 ] : '';
									$next = isset( $tasks_list['posts'][ $k + 1 ] ) ? $tasks_list['posts'][ $k + 1 ] : '';
								}
							}
						}
						?>
                        <div class="nav_links dashicons-arrow-left-alt2 <?php echo ! $prev ? 'inactive' : ''; ?> "><?php if ( $prev ): ?>
                                <a href="#task-view-display_<?php echo $prev->ID; ?>"></a><?php endif; ?></div>
                        <div class="nav_links dashicons-arrow-right-alt2 <?php echo ! $next ? 'inactive' : ''; ?>"><?php if ( $next ): ?>
                                <a href="#task-view-display_<?php echo $next->ID; ?>"></a><?php endif; ?></div>
                    </div>
                    <div data-remodal-action="close" class="dashicons-no task_close"></div>
                </div>
				<?php $updates_statuses = array_flip( $statuses ); ?>
                <!--                <div class="control">-->
                <!--                    <h3 class="task_name">--><?php //echo $task->post_title; ?><!--</h3>-->
                <!--                </div>-->
                <div class="control url_container">
                    <div class="task-created">Created <span><?php echo date( 'm/d/Y',
								strtotime( $task->post_date ) ); ?></span></div>
                    <a target="_blank" href="<?php echo get_post_meta( $task->ID, '_task_url', true ); ?>">
						<?php
						$url = get_post_meta( $task->ID, '_task_url', true );
						$url = ( strlen( $url ) > 60 ) ? substr( $url, 0, 30 ) . '...' : $url;
						?>
						<?php echo $url; ?>
                    </a>
                </div>
				
				<?php if ( get_the_post_thumbnail_url( $task->ID, 'full' ) ): ?>
                    <div class="img_container">
                        <div class="img_wrp">
                            <div class="img">
                                <!--                            <img src="-->
								<?php //echo get_the_post_thumbnail_url( $task->ID, 'full' ); ?><!--" alt="task-image">-->
                                <div class="task-card-image"
                                     data-image-url="<?php echo get_the_post_thumbnail_url( $task->ID, 'full' ) ? get_the_post_thumbnail_url( $task->ID, 'full' ) : '' ?>">
                                    <!--                            <img id="task_image" src="-->
									<?php //echo get_the_post_thumbnail_url( $task->ID, 'full' ); ?><!--" alt="task-image">-->
                                    <canvas id="task_image_canvas_<?php echo $task->ID; ?>">
                                </div>
								<?php
								$canvas_data = get_post_meta( $task->ID, 'canvas_data', true );
								if ( $canvas_data ) {
									$rects        = $canvas_data['canvas_data'];
									$descriptions = $canvas_data['rect_description'];
								}
								?>
                                <input type="hidden" name="rects_data"
                                       value="<?php echo htmlspecialchars( json_encode( $rects ) ); ?>">
                                <input type="hidden" name="rects_descriptions"
                                       value="<?php echo stripslashes( htmlspecialchars( json_encode( $descriptions ) ) ); ?>">

                                <div class="dashicons-editor-expand image_expand image_control"></div>
                                <div class="dashicons-editor-contract  image_contract image_control"></div>
                            </div>
                        </div>
                    </div>
				<?php endif; ?>
				<?php
				$args = [
					'post_id' => $task->ID,
					'status'  => 'approve',
					'orderby' => 'ID',
					'order'   => 'ASC',
				];
				?>
				<?php if ( ! empty( get_comments( $args ) ) ): ?>
                    <div class="comment-section">
                        <div class="task-card-comments-wrap task-id-<?php echo $task->ID; ?>">
							<?php $this->get_comment_list_html( $task->ID ); ?>
                        </div>
                    </div>
				<?php else: ?>
                    <div class="comment-section">
                        <div class="task-card-comments-wrap empty task-id-<?php echo $task->ID; ?>">
                        </div>
                    </div>
				<?php endif; ?>
                <div class="comment-section">
                    <div class="comment-form-wrap">
                        <form class="task-card-comment-submit-box comment-form">
                            <div class="ns-error-box"></div>
                            <input type="hidden" name="post_id" value="<?php echo $task->ID; ?>">
                            <div class="text-wrap">
                                <textarea class="task-card-textarea" placeholder="Add Comment" rows="4"
                                          name="description"></textarea>
                            </div>
                            <div class="button-wrap">
                                <div class="task_approval_wrp text-center">
									<?php if ( $this->task_settings->is_aproved_on_live( $task->post_status ) || $this->task_settings->is_closed( $task->post_status ) ): ?>
										<?php if ( ! $this->task_settings->is_aproved_on_live( $task->post_status ) ) : ?>
                                            <button data-remodal-action="close"
                                                    class="ns_green_button reject_task orange_bg reopen"
                                                    data-task-id="<?php echo $task->ID; ?>">
                                                Reopen
                                            </button>
										<?php else : ?>
                                            <button class="ns_green_button reject_task orange_bg"
                                                    data-task-id="<?php echo $task->ID; ?>">
                                                Reject
                                            </button>
											<?php if ( ! get_post_meta( $task->ID, 'task_live_approved', true ) ) : ?>
                                                <button data-remodal-action="close"
                                                        class="ns_green_button live_approval <?php echo get_post_meta( $task->ID, 'task_live_approved', true ) ? 'grey_bg closed' : ''; ?>"
                                                        data-task-id="<?php echo $task->ID; ?>">
													<?php echo get_post_meta( $task->ID, 'task_live_approved', true ) ? 'Closed' : 'Approve & Close'; ?>
                                                </button>
											<?php endif; ?>
										<?php endif; ?>
									<?php endif; ?>
                                </div>
                                <button type="submit" class="task-card-comment-submit ns_green_button">Comment</button>
                            </div>
                        </form>
                    </div>
                </div>
				<?php if ( $task->post_status == $this->task_settings->get_default_status() ): ?>
                    <div class="dashicons-trash task_delete"></div>
				<?php endif; ?>
            </div>
        </section>
	<?php endforeach; ?>
<?php endif; ?>
<?php //if (!$tasks_list['hide_load_button']): ?>
<!--    <div class="load-more-wrap text-center">-->
<!--        <button class="load-more ns_button_grey" data-statuses="--><?php //echo implode(',', $tasks_list['statuses']); ?><!--" data-offset="--><?php //echo $tasks_list['new_offset']; ?><!--" >Load More</button>-->
<!--    </div>-->
<?php //endif; ?>
