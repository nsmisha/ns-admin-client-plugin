<?php
$projects = $this->get_projects();
?>
<div class="client-task-creation-container container">
    <div class="client-task-creation-grid">
        <div class="client-task-col">
            <div class="request-container task-container">
                <span class="ns_sorting left_square dashicons-arrow-down-alt2" data-task-container="in_progress"
                      data-order-by="_task_id_hub"
                      data-order="<?php echo $this->get_tasks_order() == 'desc' ? 'asc' : 'desc'; ?>"></span>
                <div class="client-task-title ns_title">
					<?php echo __( 'In Progress', 'ns_admin' ); ?>
                    <div class="new_task_btn">
                        <button type="button" data-remodal-target="task-create-display"
                                name="button"
                                class="new-request-button ns_button orange"
                                id="new-request-button">
                            <span>+</span>
                        </button>
                    </div>
                </div>
                <section class="new-task-input-container create_new_task_modal remodal"
                         data-remodal-id="task-create-display"
                         data-remodal-options="hashTracking: false, closeOnOutsideClick: true">
                    <div>
                        <div data-remodal-action="close" class="remodal-close">
                            <span data-remodal-action="close" class="back">Back</span>
                        </div>
                        <form id="ns-task-form">
                            <div class="ns-error-box"></div>
                            <div class="control">
                                <input type="text" class="task-title" placeholder="Enter Task Title" name="title">
                            </div>
                            <div class="grid url-grid">
                                <div class="control">
                                    <input type="text" class="task-url" name="url"
                                           placeholder="Enter URL (i.e. https://neversettle.it)">
                                </div>
                                <div class="control">
                                    <div class="image-control">
										<?php if ( current_user_can( 'upload_files' ) ): ?>
                                            <div class="image-button-wrap">
                                                <input class="image-uploader" id="image-uploader" type="button"
                                                       value="Upload Image">
                                            </div>
                                            <input type="hidden" name="_thumbnail_id" id="_thumbnail_id"/>
										<?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="image-preview-wrap">
                                <img id="image-uploader-image"/>
                            </div>
                            <div class="clearfix"></div>
                            <div class="control textarea_control">
                                <textarea class="task-description" rows="6" name="description"></textarea>
                            </div>
                            <div class="select-submit-box text-right">
                                <button id="task-submit" class="task-submit ns_button_grey orange" type="button"
                                        form="task-form" value="submit">Add Task
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
                <div class="task-list-container">
                    <div class="request-list-wrap">
                        <div class="grid task-list-wrap in_progress_tasks_wrap ns_sortable_task_container">
							<?php $this->get_task_list_html( $this->task_settings->get_client_in_progress() ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="client-task-col">
            <div class="staging-container task-container">
                <span class="ns_sorting left_square dashicons-arrow-down-alt2" data-task-container="ready_to_test"
                      data-order-by="_task_id_hub"
                      data-order="<?php echo $this->get_tasks_order() == 'desc' ? 'asc' : 'desc'; ?>"></span>
                <div class="client-task-title ns_title">
					<?php echo __( 'Ready To Test', 'ns_admin' ); ?>
                </div>
                <span class="ns_sorting right_square dashicons-arrow-down-alt2" data-task-container="ready_to_test"
                      data-order-by="post_date"
                      data-order="<?php echo $this->get_tasks_order() == 'desc' ? 'asc' : 'desc'; ?>"></span>
                <div class="staging-list-wrap task-list-wrap">
					<?php $this->get_task_list_html( $this->task_settings->get_approved_live_statuses() ); ?>
                </div>
            </div>
            <div class="complete-container task-container">
                <span class="ns_sorting left_square dashicons-arrow-down-alt2" data-task-container="complete"
                      data-order-by="_task_id_hub"
                      data-order="<?php echo $this->get_tasks_order() == 'desc' ? 'asc' : 'desc'; ?>"></span>
                <div class="client-task-title ns_title">
                    <div class="filter-complete-container ns_add_new_wrp">
                        <span><?php echo __( 'Complete', 'ns_admin' ); ?></span>
                        <!--                        <div class="completed_sorting_wrp">-->
                        <!--                            <div class="ns_create_wrap">-->
                        <!--                                <h5 class="ns_category_title">-->
						<?php //echo __('Sort By:', 'ns_admin'); ?><!--</h5>-->
                        <!--                            </div>-->
                        <!--                            <div class="filter-complete-container-checkboxes">-->
                        <!--                                <div class="checkbox_wrp">-->
                        <!--                                    <input type="radio" name="sort_completed" id="menu_order" class="tog complete-sort-button ns_category_sort_button -->
						<?php //echo $this->get_tasks_order_by() == 'menu_order' ? 'current' : '' ?><!--" data-order-by="menu_order" data-order="-->
						<?php //echo $this->get_tasks_order() == 'desc' ? 'asc': 'desc'; ?><!--">-->
                        <!--                                    <label for="menu_order">-->
						<?php //echo __('Bug', 'ns_admin'); ?><!--</label>-->
                        <!--                                </div>-->
                        <!--                                <div class="checkbox_wrp">-->
                        <!--                                    <input type="radio" name="sort_completed" id="modified" class="tog complete-sort-button ns_category_sort_button -->
						<?php //echo $this->get_tasks_order_by() == 'modified' ? 'current' : '' ?><!--" data-order-by="modified" data-order="-->
						<?php //echo $this->get_tasks_order() == 'desc' ? 'asc': 'desc'; ?><!--">-->
                        <!--                                    <label for="modified">-->
						<?php //echo __('Completion', 'ns_admin'); ?><!--</label>-->
                        <!--                                </div>-->
                        <!--                                <div class="checkbox_wrp">-->
                        <!--                                    <input type="radio" name="sort_completed" id="date"  class="tog complete-sort-button ns_category_sort_button -->
						<?php //echo $this->get_tasks_order_by() == 'date' ? 'current' : '' ?><!--" data-order-by="date" data-order="-->
						<?php //echo $this->get_tasks_order() == 'desc' ? 'asc': 'desc'; ?><!--">-->
                        <!--                                    <label for="date">-->
						<?php //echo __('Creation', 'ns_admin'); ?><!--</label>-->
                        <!--                                </div>-->
                        <!--                            </div>-->
                        <!--                        </div>-->
                    </div>
                </div>
                <span class="ns_sorting right_square dashicons-arrow-down-alt2" data-task-container="complete"
                      data-order-by="modified"
                      data-order="<?php echo $this->get_tasks_order() == 'desc' ? 'asc' : 'desc'; ?>"></span>
                <div class="complete-list-wrap task-list-wrap">
					<?php $this->get_task_list_html( $this->task_settings->get_closed_statuses(), [
						'order_by' => $this->get_tasks_order_by(),
						'order'    => $this->get_tasks_order(),
					] ); ?>
                </div>
            </div>
        </div>
    </div>
</div>