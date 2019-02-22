<?php
global $task;
$statuses = $this->task_settings->get_statuses();
$client   = get_post_meta( $task->ID, '_client_id', true );
$assignee = get_post_meta( $task->ID, '_assignee_name', true );
$reporter = get_post_meta( $task->ID, '_reporter_name', true );
$reporter = $reporter ?: get_post_meta( $task->ID, '_reporter_name_client', true );
$project  = get_post_meta( $task->ID, '_project_id', true );

?>
<div class="task-edit-wrap">
    <div class="grid edit-task">
        <div class="col-3-12">
            <div class="grid">
                <div class="col-6-12">
                    <label for="ns_client_id">Client:</label>
                    <span class="select-control-fake"><?php echo get_bloginfo( 'name' ); ?></span>
                </div>
                <div class="col-6-12">
                    <label for="ns_project_id">Project:</label>
                    <span class="select-control-fake"><?php echo $project ?: 'Is not selected'; ?></span>
                </div>
            </div>
        </div>
        <div class="col-6-12">
            <div class="task-statuses-wrap text-center">
				<?php foreach ( $statuses as $k => $st ): if ( $st === $this->task_settings->get_default_status() ) {
					continue;
				} ?>
					<?php
					$updates_statuses = array_flip( $statuses );
					$prev_status      = $statuses[ $k - 1 ];
					$checked          = $k <= $updates_statuses[ $task->post_status ] ? ' checked ' : '';
					$next_st_key      = $updates_statuses[ $task->post_status ] + 1;
					$next_st          = isset( $statuses[ $next_st_key ] ) ? $statuses[ $next_st_key ] : 0;
					$next_checked     = $k <= $next_st_key ? ' checked ' : '';
					
					$readonly = $this->task_settings->is_available_for_client( $st )
					            && ! $checked
					            && ( in_array( $task->post_status, array_merge( $this->task_settings->get_fixed_staging_statuses(), $this->task_settings->get_pushed_live_statuses() ) )
					                 && $next_checked )
						? ''
						: ' readonly ';
					?>
                    <!-- .status-checkbox -->
                    <div class="status-checkbox <?php echo $readonly; ?>">
                        <input type="checkbox" value="None" id="status-checkbox-<?php echo $st ?>" name="ns_status[]"
                               data-task-id="<?php echo $task->ID; ?>" data-status-prev="<?php echo $prev_status; ?>"
                               data-status="<?php echo $st; ?>" <?php echo $checked; ?> <?php echo $readonly; ?>/>
                        <label for="status-checkbox-<?php echo $st ?>"></label>
                        <span class="status-name"><?php echo $this->task_settings->prepare_status( $st ); ?></span>
                        <span class="locked"></span>
                    </div>
				<?php endforeach; ?>
                <div class="line"></div>
            </div>
        </div>
        <div class="col-3-12">
            <div class="grid">
                <div class="col-6-12">
                    <label for="ns_assignee">Assignee</label>
                    <span class="select-control-fake"><?php echo $assignee ?: 'Is not selected'; ?></span>
                </div>
                <div class="col-6-12">
                    <label for="ns_reporter">Reporter</label>
                    <span class="select-control-fake"><?php echo $reporter ?: 'Is not selected'; ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="grid">
        <div class="col-4-12">
            <div class="task-info">
                <div class="state-label">
					<?php $state = $this->task_settings->get_state( $task->post_status ); ?>
                    <span class="state-<?php echo $state; ?>"><?php echo $state; ?></span>
                </div>
                <div class="task-title-box">
                    <div class="task-title">#<?php echo $task->ID; ?> <?php echo $task->post_title; ?></div>
                    <div class="task-created">Created
                        on <?php echo date( 'm/d/Y @ g:i a', strtotime( $task->post_date ) ); ?></div>
                </div>
                <div class="task-url">
                    <a href="<?php echo get_post_meta( $task->ID, '_task_url', true ); ?>"><?php echo get_post_meta( $task->ID, '_task_url', true ); ?></a>
                </div>
            </div>
            <div class="comment-section">
                <div class="task-card-comments-wrap task-id-<?php echo $task->ID; ?>">
					<?php $this->get_comment_list_html( $task->ID ); ?>
                </div>
                <div class="comment-form-wrap">
                    <form class="task-card-comment-submit-box comment-form">
                        <div class="ns-error-box"></div>
                        <input type="hidden" name="post_id" value="<?php echo $task->ID; ?>">
                        <div class="text-wrap">
                            <textarea class="task-card-textarea" placeholder="Add Comment" rows="4"
                                      name="description"></textarea>
                        </div>
                        <div class="button-wrap">
                            <button type="submit" class="task-card-comment-submit ns_button_grey">Add</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="recent-tasks">
				<?php $this->get_task_list_html( array_diff( $this->task_settings->get_statuses(), array_merge( $this->task_settings->get_closed_statuses(), [ $this->task_settings->get_default_status() ] ) ), [ 'limit' => 10 ] ); ?>
            </div>
            <!--<div class="closed-title">-->
            <!--    Closed Tasks-->
            <!--</div>-->
            <!--<div class="closed-tasks">-->
            <!--    --><?php //$this->get_task_list_html($this->get_closed_statuses(), ['client_id' => $client_id, 'limit' => 10]); ?>
            <!--</div>-->
        </div>
        <div class="col-8-12">
            <div class="task-card-image">
				<?php if ( get_the_post_thumbnail_url( $task->ID, 'full' ) ): ?>
                    <img src="<?php echo get_the_post_thumbnail_url( $task->ID, 'full' ); ?>" alt="task-image">
				<?php endif; ?>
            </div>
        </div>
    </div>
</div>
