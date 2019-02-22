<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/4.2.2/d3.min.js"></script>-->
<!--<script src="http://d3js.org/d3.v4.min.js"></script>-->
<?php
$timeline_meta_data = get_site_option( 'received_data' );
$client_timeline    = unserialize( $timeline_meta_data['projects_data'] );
$connection_status  = get_site_option( 'connection_to_hub_status' );
?>
<?php if ( isset ( $client_timeline[0]['timeline'] ) && count( $client_timeline[0]['timeline'] ) > 1 && $connection_status == 'success' ): ?>
    <div class="timeline_management_block">
		<?php $project_count = 0; ?>
		<?php foreach ( $client_timeline as $project ): ?>
			<?php
			$project_count ++;
			$active_timeline_class = $project_count == 1 ? 'cd-horizontal-timeline active_timeline' : '';
			$visible_timeline      = $project_count == 1 ? '' : 'style="display: none;"';
			$collapse_expand       = $project_count == 1 ? 'Collapse' : 'Expand';
			?>
            <div class="project_header">
                <h5 class="project_title">
					<?php echo '<span>' . $project['project_name'] . '</span> ' . __( 'Timeline and Milestones', 'ns_admin' ); ?>
                </h5>
                <span class="expand_item <?php echo $project_count == 1 ? 'expanded' : 'collapsed'; ?>">
                        <?php echo $collapse_expand; ?>
                    </span>
            </div>
            <div class="project_wrp ">
                <div class="timeline_header">
                    <h4 class="col-4-12"><?php echo __( 'Project Scope Summary', 'ns_admin' ); ?></h4>
                    <h4 class="col-4-12 timeline_title"><?php echo __( 'Timeline', 'ns_admin' ); ?></h4>
                    <div class="timeline_index_wrp col-4-12">
                        <div class="timeline_index complete"><?php echo __( 'Complete', 'ns_admin' ); ?></div>
                        <div class="timeline_index current"><?php echo __( 'Current Milestone', 'ns_admin' ); ?></div>
                    </div>
                </div>
				<?php $current_sprint_item = array_search( '', array_column( $project['timeline'], 'completion' ) ); ?>
                <div class="project_description col-4-12">
					<?php echo $project['project_summary']; ?>
                </div>
				<?php if ( $project['timeline'] ) : ?>
                    <section class="cd-horizontal-timeline col-8-12">
                        <div class="timeline">
                            <div class="events-wrapper">
                                <div class="events" id="events">
                                    <ol>
										<?php
										$counter            = 0;
										$first_not_selected = false;
										foreach ( $project['timeline'] as $key => $single_point ) {
											if ( $current_sprint_item === $key ) {
												$first_not_selected = true;
											}
											$selected = $first_not_selected ? 'class="selected"' : '';
											if ( ! empty( $single_point['date'] ) && ! empty( $single_point['name'] ) ) :?>
                                                <li>
                                                    <a href="#0"
                                                       data-date="<?php echo $counter; ?>/01/2017" <?php echo $selected; ?>>
														<?php echo date( 'M d', strtotime( $single_point['date'] ) ); ?>
                                                        <br>
                                                        <span>
                                                            <span>
                                                                <?php
                                                                if ( strlen( $single_point['name'] ) > 30 ) {
	                                                                $single_point['name'] = substr( $single_point['name'], 0, 40 ) . '...';
                                                                }
                                                                ?>
                                                                <?php echo $single_point['name']; ?>
                                                            </span>
                                                        </span>
                                                    </a>
                                                </li>
											<?php endif;
											$first_not_selected = false;
											$counter ++;
										}
										?>
                                        <svg class="timelinesvg" width="100%" height="300">
                                            <defs>
                                                <clipPath id="cut_off_new<?php echo $project_count; ?>">
                                                    <rect x="0" y="0" width="0" height="300"/>
                                                </clipPath>
                                                <clipPath id="cut_off_old<?php echo $project_count; ?>">
                                                    <rect x="0" y="0" width="0" height="300"/>
                                                </clipPath>
                                            </defs>
                                            <path clip-path="url(#cut_off_old<?php echo $project_count; ?>)" class="new"
                                                  d=""></path>
                                            <path clip-path="url(#cut_off_new<?php echo $project_count; ?>)" class="old"
                                                  d=""></path>
                                        </svg>
                                    </ol>
                                    <span style="display: none;" class="filling-line" aria-hidden="true"></span>
                                </div> <!-- .events -->
                            </div> <!-- .events-wrapper -->
                            <!--            <ul class="cd-timeline-navigation">-->
                            <!--                <li><a href="#0" class="prev inactive">Prev</a></li>-->
                            <!--                <li><a href="#0" class="next">Next</a></li>-->
                            <!--            </ul> <!-- .cd-timeline-navigation -->
                        </div> <!-- .timeline -->


                        <!--        DO NOT DELETE THIS IS EXAMPLE OF DISPLAY TIMELINE ITEMS CONTENT-->
                        <!--        <div class="events-content" style="display: none;">-->
                        <!--            <ol>-->
                        <!--                --><?php
						//
						//                $counter_names = 7;
						//                $first_not_selected_names = false;
						//                foreach ( $client_timeline as $key => $single_point ) {
						//                    if ( $current_sprint_item === $key ) {
						//                        $first_not_selected_names = true;
						//                    }
						//                    $selected = $first_not_selected_names ? 'class="selected"' : '';
						//                    ?>
                        <!--                    <li data-date="--><?php //echo $counter_names; ?><!--/01/2017" -->
						<?php //echo $selected; ?><!-->
                        <!--                        <p>-->
                        <!--                            --><?php //echo $single_point['name']; ?>
                        <!--                        </p>-->
                        <!--                    </li>-->
                        <!--                    --><?php
						//                    $first_not_selected_names = false;
						//                    $counter_names++;
						//                }
						//                ?>
                        <!---->
                        <!--            </ol>-->
                        <!--        </div> <!-- .events-content -->
                    </section>
				<?php else : ?>
                    <h4 class="empty_timeline"><?php echo __( 'No Timelines Yet', 'ns_admin' ); ?></h4>
				<?php endif; ?>
            </div>
		<?php endforeach; ?>

        <style>
            .timelinesvg path {
                fill: none;
            }

            .timelinesvg circle {
                fill: none;
            }
        </style>

    </div>
<?php endif; ?>
<div id="chart"></div>

