<?php
$ns_wp_documentation = get_site_option( 'ns_admin_documentation' );
?>
<div class="documentation repeater" id="repeatable-fieldset-one">
    <div class="docs_header">
        <h3 class="documentations_title">
			<?php echo __( 'Documentation', 'ns_admin' ); ?>
        </h3>
        <div class="doc_categories">
            <ul class="documentation_tabs">
                <li data-tab="tab-1" class="current">Help Documents</li>
                <li data-tab="tab-2">Testing Plan</li>
                <li data-tab="tab-3">Developer Notes</li>
            </ul>
            <input data-repeater-create id="add-row" type="button" value="+"/>
            <div class="clear"></div>
        </div>
    </div>
	<?php
	if ( $ns_wp_documentation > 0 ): ?>
        <div class="doc-content current" id="tab-1">
            <div data-repeater-list="documentations" data-sortable-id="sortable" id="sortable-1">
				<?php
				foreach ( $ns_wp_documentation as $tab => $tab_content ):
					if ( $tab == 'tab-1' ):
						if ( $tab_content !== 'false' ):
							foreach ( $tab_content as $editor_id => $value ):
								foreach ( $value as $title => $content ):
									if ( $title !== 0 ) :
										?>
                                        <div data-repeater-item="<?php echo $editor_id; ?>" class="repeater_item">
                                            <div class="ns_doc_title_block"
                                                 data-remodal-target="<?php echo $editor_id; ?>">
                                                <h2><?php echo $title; ?></h2>
                                            </div>
                                            <div class="ns_doc_edit remodal"
                                                 data-remodal-id="<?php echo $editor_id; ?>">
                                                <div class="doc_header">
                                                    <button data-remodal-action="close"
                                                            class="close_users_modal dashicons-arrow-left-alt2">Back
                                                    </button>
                                                    <a class="doc_edit"></a>
                                                </div>
                                                <div class="editor_area">
                                                    <input type="text" name="documentation_editor"
                                                           value="<?php echo $title; ?>" class="documentation_title"
                                                           style="width:100%;">
													<?php
													wp_editor( $content, $editor_id, $settings = array( 'textarea_rows' => 13, ) );
													?>
                                                    <input data-repeater-delete class="remove-row" type="button"
                                                           value="Delete"/>
                                                    <button class="save_documentation">Save</button>
                                                    <div class="clear"></div>
                                                </div>
                                                <div class="saved_content">
                                                    <h3><?php echo $title; ?></h3>
													<?php
													echo wpautop( $content );
													?>
                                                </div>
                                            </div>
                                        </div>
									<?php
									endif;
								endforeach;
							endforeach;
						else: ?>
                            <!--                                    <div data-repeater-item class="repeater_item">-->
                            <!--                                        <div class="ns_doc_title_block">-->
                            <!--                                            <h2>Title</h2>-->
                            <!--                                        </div>-->
                            <!--                                        <div class="ns_doc_edit" style="display: block;">-->
                            <!--                                            <label>-->
                            <!--                                                <h3>Doc Title</h3>-->
                            <!--                                                <input type="text" name="documentation_editor" class="documentation_title" style="width:100%;">-->
                            <!--                                            </label>-->
                            <!--											--><?php
//												$textarea_id = 'custom_id_tab_1-' . mt_rand(100000,999999);
//												wp_editor('', $textarea_id, $settings = array( 'textarea_rows' => 8 ));
//											?>
                            <!--                                            <input data-repeater-delete class="remove-row" type="button" value="Delete"/>-->
                            <!--                                        </div>-->
                            <!--                                    </div>-->
						<?php endif;
					endif;
				endforeach;
				?>
            </div>
        </div>
        <div class="doc-content" id="tab-2">
            <div data-repeater-list="documentations" data-sortable-id="sortable" id="sortable-2">
				<?php
				foreach ( $ns_wp_documentation as $tab => $tab_content ):
					if ( $tab == 'tab-2' ):
						if ( $tab_content !== 'false' ):
							foreach ( $tab_content as $editor_id => $value ):
								foreach ( $value as $title => $content ):
									if ( $title !== 0 ) :
										?>
                                        <div data-repeater-item="<?php echo $editor_id; ?>" class="repeater_item">
                                            <div class="ns_doc_title_block"
                                                 data-remodal-target="<?php echo $editor_id; ?>">
                                                <h2><?php echo $title; ?></h2>
                                            </div>
                                            <div class="ns_doc_edit remodal"
                                                 data-remodal-id="<?php echo $editor_id; ?>">
                                                <div class="doc_header">
                                                    <button data-remodal-action="close"
                                                            class="close_users_modal dashicons-arrow-left-alt2">Back
                                                    </button>
                                                    <a class="doc_edit"></a>
                                                </div>
                                                <div class="editor_area">
                                                    <input type="text" name="documentation_editor"
                                                           value="<?php echo $title; ?>" class="documentation_title"
                                                           style="width:100%;">
													<?php
													wp_editor( $content, $editor_id, $settings = array( 'textarea_rows' => 13, ) );
													?>
                                                    <input data-repeater-delete class="remove-row" type="button"
                                                           value="Delete"/>
                                                    <button class="save_documentation">Save</button>
                                                    <div class="clear"></div>
                                                </div>
                                                <div class="saved_content">
                                                    <h3><?php echo $title; ?></h3>
													<?php
													echo wpautop( $content );
													?>
                                                </div>
                                            </div>
                                        </div>
									<?php
									endif;
								endforeach;
							endforeach;
						else: ?>
                            <!--                                    <div data-repeater-item class="repeater_item">-->
                            <!--                                        <div class="ns_doc_title_block">-->
                            <!--                                            <h2>Title</h2>-->
                            <!--                                        </div>-->
                            <!--                                        <div class="ns_doc_edit" style="display: block;">-->
                            <!--                                            <label>-->
                            <!--                                                <h3>Doc Title</h3>-->
                            <!--                                                <input type="text" name="documentation_editor" class="documentation_title" style="width:100%;">-->
                            <!--                                            </label>-->
                            <!--											--><?php
//												$textarea_id = 'custom_id_tab_2-' . mt_rand(100000,999999);
//												wp_editor('', $textarea_id, $settings = array( 'textarea_rows' => 8 ));
//											?>
                            <!--                                            <input data-repeater-delete class="remove-row" type="button" value="Delete"/>-->
                            <!--                                        </div>-->
                            <!--                                    </div>-->
						<?php endif;
					endif;
				endforeach;
				?>
            </div>
        </div>
        <div class="doc-content" id="tab-3">
            <div data-repeater-list="documentations" data-sortable-id="sortable" id="sortable-3">
				<?php
				$tab_3_docs_counter = 0;
				foreach ( $ns_wp_documentation as $tab => $tab_content ):
					if ( $tab == 'tab-3' ):
						if ( $tab_content !== 'false' ):
							foreach ( $tab_content as $editor_id => $value ):
								foreach ( $value as $title => $content ):
									if ( $title !== 0 ) :
										?>
                                        <div data-repeater-item="<?php echo $editor_id; ?>" class="repeater_item">
                                            <div class="ns_doc_title_block"
                                                 data-remodal-target="<?php echo $editor_id; ?>">
                                                <h2><?php echo $title; ?></h2>
                                            </div>
                                            <div class="ns_doc_edit remodal"
                                                 data-remodal-id="<?php echo $editor_id; ?>">
                                                <div class="doc_header">
                                                    <button data-remodal-action="close"
                                                            class="close_users_modal dashicons-arrow-left-alt2">Back
                                                    </button>
                                                    <a class="doc_edit"></a>
                                                </div>
                                                <div class="editor_area">
                                                    <input type="text" name="documentation_editor"
                                                           value="<?php echo $title; ?>" class="documentation_title"
                                                           style="width:100%;">
													<?php
													wp_editor( $content, $editor_id, $settings = array( 'textarea_rows' => 13, ) );
													?>
                                                    <input data-repeater-delete class="remove-row" type="button"
                                                           value="Delete"/>
                                                    <button class="save_documentation">Save</button>
                                                    <div class="clear"></div>
                                                </div>
                                                <div class="saved_content">
                                                    <h3><?php echo $title; ?></h3>
													<?php
													echo wpautop( $content );
													?>
                                                </div>
                                            </div>
                                        </div>
									<?php
									endif;
								endforeach;
							endforeach;
						else: ?>
                            <!--                                    <div data-repeater-item class="repeater_item">-->
                            <!--                                        <div class="ns_doc_title_block">-->
                            <!--                                            <h2>Title</h2>-->
                            <!--                                        </div>-->
                            <!--                                        <div class="ns_doc_edit" style="display: block;">-->
                            <!--                                            <label>-->
                            <!--                                                <h3>Doc Title</h3>-->
                            <!--                                                <input type="text" name="documentation_editor" class="documentation_title" style="width:100%;">-->
                            <!--                                            </label>-->
                            <!--											--><?php
//												$textarea_id = 'custom_id_tab_3-' . mt_rand(100000,999999);
//												wp_editor('', $textarea_id, $settings = array( 'textarea_rows' => 8 ));
//											?>
                            <!--                                            <input data-repeater-delete class="remove-row" type="button" value="Delete"/>-->
                            <!--                                        </div>-->
                            <!--                                    </div>-->
						<?php endif;
					endif;
				endforeach;
				?>
            </div>
        </div>
	<?php else: ?>
        <div class="doc-content current" id="tab-1">
            <div data-repeater-list="documentations" data-sortable-id="sortable" id="sortable-1">
                <!--                    <div data-repeater-item class="repeater_item">-->
                <!--                        <div class="ns_doc_title_block">-->
                <!--                            <h2>Title</h2>-->
                <!--                        </div>-->
                <!--                        <div class="ns_doc_edit" style="display: block;">-->
                <!--                            <label>-->
                <!--                                <h3>Doc Title</h3>-->
                <!--                                <input type="text" name="documentation_editor" class="documentation_title" style="width:100%;" value="Add your title here">-->
                <!--                            </label>-->
                <!--							--><?php
				//								$textarea_id = 'empty_custom_id_tab_1-' . mt_rand(100000,999999);
				//								wp_editor('', $textarea_id, $settings = array( 'textarea_rows' => 8 ));
				//							?>
                <!--                            <input data-repeater-delete class="remove-row" type="button" value="Delete"/>-->
                <!--                        </div>-->
                <!--                    </div>-->
            </div>
        </div>
        <div class="doc-content" id="tab-2">
            <div data-repeater-list="documentations" data-sortable-id="sortable" id="sortable-2">
                <!--                    <div data-repeater-item class="repeater_item">-->
                <!--                        <div class="ns_doc_title_block">-->
                <!--                            <h2>Title</h2>-->
                <!--                        </div>-->
                <!--                        <div class="ns_doc_edit" style="display: block;">-->
                <!--                            <label>-->
                <!--                                <h3>Doc Title</h3>-->
                <!--                                <input type="text" name="documentation_editor" class="documentation_title" style="width:100%;" value="Add your title here">-->
                <!--                            </label>-->
                <!--							--><?php
				//								$textarea_id = 'empty_custom_id_tab_2-' . mt_rand(100000,999999);
				//								wp_editor('', $textarea_id, $settings = array( 'textarea_rows' => 8 ));
				//							?>
                <!--                            <input data-repeater-delete class="remove-row" type="button" value="Delete"/>-->
                <!--                        </div>-->
                <!--                    </div>-->
            </div>
        </div>
        <div class="doc-content" id="tab-3">
            <div data-repeater-list="documentations" data-sortable-id="sortable" id="sortable-3">
                <!--                    <div data-repeater-item class="repeater_item">-->
                <!--                        <div class="ns_doc_title_block">-->
                <!--                            <h2>Title</h2>-->
                <!--                        </div>-->
                <!--                        <div class="ns_doc_edit" style="display: block;">-->
                <!--                            <label>-->
                <!--                                <h3>Doc Title</h3>-->
                <!--                                <input type="text" name="documentation_editor" class="documentation_title" style="width:100%;" value="Add your title here">-->
                <!--                            </label>-->
                <!--							--><?php
				//								$textarea_id = 'empty_custom_id_tab_3-' . mt_rand(100000,999999);
				//								wp_editor('', $textarea_id, $settings = array( 'textarea_rows' => 8 ));
				//							?>
                <!--                            <input data-repeater-delete class="remove-row" type="button" value="Delete"/>-->
                <!--                        </div>-->
                <!--                    </div>-->
            </div>
        </div>
	<?php endif; ?>
</div>
