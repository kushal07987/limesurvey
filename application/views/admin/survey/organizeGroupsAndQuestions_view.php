<?php 
	App()->getClientScript()->registerPackage('jquery-nestedSortable');
	App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . 'organize.js');
	
	// get question types
	$qtypes = getQuestionTypeList('', 'array');
	
	// get baselanguage to display, and lanquages of survey
	$baselang = Survey::model()->findByPk($surveyid)->language;
	$languagelist = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
	$languagelist[]=Survey::model()->findByPk($iSurveyID)->language;
	
	// if survey is published $ isNotActive is TRUE
	$isNotActive=Survey::model()->findByPk($surveyid)->active=="N";
?>

<div class='header ui-widget-header'><?php $clang->eT('Organize question groups/questions');?></div>
<p>
	<?php // $clang->eT("To reorder questions/questiongroups just drag the question/group with your mouse to the desired position.");?><br />
	<?php // $clang->eT("After you are done please click the bottom 'Save' button to save your changes.");?>
</p>

<div class='movableList'>
	<div class='organizeHeader'>
		<img src='<?php echo $sImageURL; ?>org_handle_20.png' /> <?php $clang->eT("Drag these icons below to move groups and questions up and down, click to collapse or expand them."); ?><br />


		<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' />
		<?php $clang->eT("After you are done please click the bottom 'Save' button to save your changes.");?><br/><br/>
		<div>
			<div class="actions"><a href="#" data-view="collapse"><?php $clang->eT("Collapse all groups"); ?></a> / </div>
			<div class="actions"><a href="#" data-view="expand"><?php $clang->eT("Expand all groups"); ?></a></div>
			<div class="actions">&nbsp;&nbsp;---&nbsp;&nbsp;</div>
			<div class="actions"><a href="#" data-questview="collapse"><?php $clang->eT("Collapse all questions"); ?></a> / </div>
			<div class="actions"><a href="#" data-questview="expand"><?php $clang->eT("Expand all questions"); ?></a></div>
		</div>
	</div>
	<div style="float:none;">&nbsp;</div>
	<ol class="organizer group-list" data-level='group'>
		<?php

		// show 'add group' bar if survey is empty 
		if (count($aGroupsAndQuestions)==0 && $isNotActive==1 && Permission::model()->hasSurveyPermission($surveyid,'surveycontent','create'))
		{ ?>

			<div class='add-group-item'>
				<a href="<?php echo $this->createUrl("admin/questiongroups/sa/add/surveyid/$surveyid"); ?>"><?php $clang->eT("Add new group to survey"); ?> <img src='<?php echo $sImageURL; ?>org_add_20.png'  alt='' /></a>
			</div><?php
		} 

		// loop through all groups
		foreach ($aGroupsAndQuestions as  $aGroupAndQuestions) 
		{ ?>
			<li id='list_g<?php echo $aGroupAndQuestions['gid'];?>' class='group-item' data-level='group'>
			<div class='ui-widget-header'>
				<div class='gq-leftcol'>
					<div class='lefticons'>
					
						<a href="#" data-view="<?php echo $aGroupAndQuestions['gid']; ?>"><img src='<?php echo $sImageURL; ?>org_handle_20.png' class='handle' alt='<?php $clang->eT("Drag to move"); ?><br /><?php $clang->eT("Click to collapse/expand group"); ?>' /></a>

						<input type='checkbox' class="organizer" data='gMark_<?php echo $aGroupAndQuestions['gid'];?>' value='<?php echo $surveyid; ?>X<?php echo $aGroupAndQuestions['gid'];?>' onclick="xmarkGroupQuestions('gMark_<?php echo $aGroupAndQuestions['gid'];?>')" style="width:12px;">
						
					</div>
				</div>
				
				<div class='gq-rightcol'>
					<div class='righticons'>
						<?php 
						// show group-previw icon
						if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read')) 
						{ ?>
							<a href='<?php echo $this->createUrl("survey/index/action/previewgroup/sid/" . $surveyid . "/gid/" . $aGroupAndQuestions['gid']); ?>' id='grouppreviewlink' target='_blank'><img src='<?php echo $sImageURL; ?>org_preview_20.png' alt='<?php $clang->eT("Preview current question group"); ?>' /></a>
							
							<?php 
							if (count($languagelist) > 1) 
							{ ?>
								<div class="popuptip" rel="grouppreviewlink"><?php $clang->eT("Preview this question group in:"); ?>
									<ul><?php 
										foreach ($languagelist as $tmp_lang)
										{ ?>
											<li><a target='_blank' href='<?php echo $this->createUrl("survey/index/action/previewgroup/sid/" . $surveyid . "/gid/lang/" . $tmp_lang); ?>' ><?php echo getLanguageNameFromCode($tmp_lang,false); ?></a></li><?php 
										} ?>
									</ul>
								</div><?php 
							} 
						} 
						else 
						{ ?>
							<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' /><?php 
						}

						// show edit-icon for group
						if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')) 
						{ ?>
							<a href='<?php echo $this->createUrl("admin/questiongroups/sa/edit/surveyid/".$surveyid."/gid/".$aGroupAndQuestions['gid']); ?>' target='_blank'><img src='<?php echo $sImageURL; ?>org_edit_20.png' alt='<?php $clang->eT("Edit current question group"); ?>' /></a><?php 
						} 
						else 
						{ ?>
							<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' /><?php 
						} ?>

						<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' />
						<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' />
						<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' />
						<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' />

						<?php 
						// show QA icon for group
						if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read')) 
						{ ?>
							<a href='<?php echo $this->createUrl("admin/expressions/sa/survey_logic_file/sid/".$surveyid."/gid/".$aGroupAndQuestions['gid']); ?>' target='_blank' ><img src='<?php echo $sImageURL; ?>org_qaok_20.png' alt='<?php $clang->eT("Check survey logic for current question group"); ?>' /></a><?php 
						} 
						else 
						{ ?>
							<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' /><?php 
						} ?>

						<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' />
						<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' />
						<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' />

						<?php 
						// show add-icon for group (add new group)
						if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','create'))
						{ ?>
							<a href='<?php echo $this->createUrl("admin/questiongroups/sa/add/surveyid/$surveyid"); ?>' target='_blank'><img src='<?php echo $sImageURL; ?>org_add_20.png' alt='<?php $clang->eT("Add new group to survey"); ?>' /></a><?php
						} 
						else 
						{ ?>
							<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' /><?php 
						} ?>

						<?php 
						// show export-group icon
						if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','export')) 
						{ ?>
							<a href='<?php echo $this->createUrl("admin/export/sa/group/surveyid/".$surveyid."/gid/".$aGroupAndQuestions['gid']);?>' target='_blank'><img src='<?php echo $sImageURL; ?>org_dumpquestion_20.png' alt='<?php $clang->eT("Export this question group"); ?>' /></a><?php 
						} 
						else 
						{ ?>
							<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' /><?php 
						} ?>

						<?php 
						// show delete-group icon
						if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','delete')) 
						{ ?>
							<a href='#' onclick="if (confirm('<?php $clang->eT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js"); ?>')) { window.open('<?php echo $this->createUrl("admin/questiongroups/sa/delete/surveyid/$surveyid/gid/{$aGroupAndQuestions['gid']}"); ?>','_top'); }"><img src='<?php echo $sImageURL; ?>org_delete_20.png'  alt='<?php $clang->eT("Delete current question group"); ?>' /></a><?php  
						} 
						else 
						{ ?>
							<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' /><?php
						} ?>

					</div>
				</div>
				<div class='gq-infocol'>
					<div class='gq-infotext'><?php echo $aGroupAndQuestions['group_name'];?></div>
				</div>
			</div>

			<?php 
			// ******** show questions now ********
			if (isset ($aGroupAndQuestions['questions']))
			{?>
				<ol id='gol_<?php echo $aGroupAndQuestions['gid']; ?>' class='question-list' data-level='question'>
				<?php
				// show 'add question bar' if there is no question in this group
				if (count($aGroupAndQuestions['questions'])==0 && $isNotActive==1 && Permission::model()->hasSurveyPermission($surveyid,'surveycontent','create'))
				{ ?>
					<div class='add-question-item'>
					<a href='<?php echo $this->createUrl("admin/questions/sa/addquestion/surveyid/".$surveyid."/gid/".$aGroupAndQuestions['gid']); ?>'><?php $clang->eT("Add new question to group"); ?> <img src='<?php echo $sImageURL; ?>org_add_20.png'  alt='' /></a>
					</div><?php
				} 

				// loop through all questions of this group
				foreach($aGroupAndQuestions['questions'] as $aQuestion)
				{?>
					<li id='list_q<?php echo $aQuestion['qid'];?>' class='question-item' data-level='question'>
						<div class='ui-widget-header question-wrapper'>
							<div class='gq-leftcol'>
								<div class='lefticons'>
									<a href="#" data-questview="<?php echo $aQuestion['qid']; ?>">
									<img src='<?php echo $sImageURL; ?>org_handle_20.png' class='handle' alt='<?php $clang->eT("Drag to move"); ?>' /></a>
									<input type='checkbox' data='qMark_<?php echo $aQuestion['gid']; ?>_<?php echo $aQuestion['qid']; ?>' value='<?php echo $aQuestion['qid'];?>' />
								</div>
							</div>

							<div class='gq-rightcol'>
								<div class='righticons'>
								<?php
								// show preview-icon (or emptyicon)
								if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read')) 
								{ ?>
									<a accesskey='q' id='questionpreviewlink' href='<?php echo $this->createUrl("survey/index/action/previewquestion/sid/" . $surveyid . "/gid/" . $aQuestion['gid'] . "/qid/" . $aQuestion['qid']); ?>' target='_blank'><img src='<?php echo $sImageURL; ?>org_preview_20.png' alt='<?php $clang->eT("Preview this question"); ?>' /></a><?php 
									if (count($languagelist) > 1) 
									{ ?><div class="popuptip" rel="questionpreviewlink"><?php $clang->eT("Preview this question in:"); ?>
											<ul><?php 
												foreach ($languagelist as $tmp_lang)
												{ ?><li><a target='_blank' href='<?php echo $this->createUrl("survey/index/action/previewquestion/sid/" . $surveyid . "/gid/" . $aQuestion['gid'] . "/qid/" . $aQuestion['qid'] . "/lang/" . $tmp_lang); ?>' ><?php echo getLanguageNameFromCode($tmp_lang,false); ?></a></li><?php
												} ?>
											</ul>
										</div><?php 
									} 
								} 
								else 
								{ ?>
									<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' /><?php 
								} 

								// show edit-icon (or emptyicon)
								if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')) 
								{ ?>
									<a href='<?php echo $this->createUrl("admin/questions/sa/editquestion/surveyid/".$surveyid."/gid/".$aQuestion['gid']."/qid/".$aQuestion['qid']); ?>'><img src='<?php echo $sImageURL; ?>org_edit_20.png' alt='<?php $clang->eT("Edit Current Question"); ?>' /></a><?php 
								} 
								else 
								{ ?>
									<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' /><?php 
								} 

								// show either subquestion-, answer-, subquestion2D-icons or 2 empty-icons instead
								if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read')) 
								{ 
									if ($qtypes[$aQuestion['type']]['subquestions'] ==0) 
									{ ?>
										<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' />
										<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' /><?php 
									} 
									elseif ($qtypes[$aQuestion['type']]['subquestions'] ==1) 
									{ 
										$sqrq = Question::model()->findAllByAttributes(array('parent_qid' => $aQuestion['qid'], 'language' => $baselang));
										$sqct = count($sqrq);
										
										if($sqct>0) 
										{ ?>
											<a href='<?php echo $this->createUrl('admin/questions/sa/subquestions/surveyid/'.$surveyid.'/gid/'.$aQuestion['gid'].'/qid/'.$aQuestion['qid']); ?>'><img src='<?php echo $sImageURL; ?>org_subquestions_20.png' alt='<?php $clang->eT("Edit subquestions for this question"); ?>' /></a><?php
										} 
										else 
										{ ?>
											<a href='<?php echo $this->createUrl('admin/questions/sa/subquestions/surveyid/'.$surveyid.'/gid/'.$aQuestion['gid'].'/qid/'.$aQuestion['qid']); ?>'><img src='<?php echo $sImageURL; ?>org_subquestionserr_20.png' alt='<?php $clang->eT("You need to add subquestions to this question"); ?>' /></a><?php 
										} 

										if($qtypes[$aQuestion['type']]['answerscales'] > 0) 
										{ 
											$qrr = Answer::model()->findAllByAttributes(array('qid' => $aQuestion['qid'], 'language' => $baselang));
											$aoct = count($qrr);

											if($aoct>0) 
											{ ?>
												<a href='<?php echo $this->createUrl('admin/questions/sa/answeroptions/surveyid/'.$surveyid.'/gid/'.$aQuestion['gid'].'/qid/'.$aQuestion['qid']); ?>'><img src='<?php echo $sImageURL; ?>org_answers_20.png' alt='<?php $clang->eT("Edit answer options for this question"); ?>' /></a><?php
											} 
											else 
											{ ?>
												<a href='<?php echo $this->createUrl('admin/questions/sa/answeroptions/surveyid/'.$surveyid.'/gid/'.$aQuestion['gid'].'/qid/'.$aQuestion['qid']); ?>'><img src='<?php echo $sImageURL; ?>org_answerserr_20.png' alt='<?php $clang->eT("You need to add answer options to this question"); ?>' /></a><?php
											} 
										} 
										else 
										{ ?>
											<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' /><?php 
										} 
									} 
									elseif ($qtypes[$aQuestion['type']]['subquestions'] ==2) 
									{ 
										$sqrq = Question::model()->findAllByAttributes(array('parent_qid' => $aQuestion['qid'], 'language' => $baselang));
										$sqct = count($sqrq);

										if($sqct>0) 
										{ ?>
											<a href='<?php echo $this->createUrl('admin/questions/sa/subquestions/surveyid/'.$surveyid.'/gid/'.$aQuestion['gid'].'/qid/'.$aQuestion['qid']); ?>'><img src='<?php echo $sImageURL; ?>org_subquestions2d_20.png' alt='<?php $clang->eT("Edit subquestions for this question"); ?>' /></a><?php
										} 
										else 
										{ ?>
											<a href='<?php echo $this->createUrl('admin/questions/sa/subquestions/surveyid/'.$surveyid.'/gid/'.$aQuestion['gid'].'/qid/'.$aQuestion['qid']); ?>'><img src='<?php echo $sImageURL; ?>org_subquestions2derr_20.png' alt='<?php $clang->eT("You need to add subquestions for this question"); ?>' /></a><?php
										} ?>
										<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' /><?php
									}

									// show edit default answers icon if required by question type
									if($qtypes[$aQuestion['type']]['hasdefaultvalues'] >0) 
									{ ?>
										<a href='<?php echo $this->createUrl('admin/questions/sa/editdefaultvalues/surveyid/'.$surveyid.'/gid/'.$aQuestion['gid'].'/qid/'.$aQuestion['qid']); ?>'><img src='<?php echo $sImageURL; ?>org_defaultanswers_20.png' alt='<?php $clang->eT("Edit default answers for this question"); ?>' /></a><?php 
									} 
									else 
									{ ?>
										<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' /><?php
									} 
								} 

								// show edit conditions icon
								if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')) 
								{ ?>
									<a href='<?php echo $this->createUrl('admin/conditions/sa/index/subaction/editconditionsform/surveyid/' . $surveyid . '/gid/' . $aQuestion['gid'] . '/qid/' . $aQuestion['qid']); ?>'><img src='<?php echo $sImageURL; ?>org_conditions_20.png' alt='<?php $clang->eT("Set conditions for this question"); ?>'  /></a><?php 
								} 
								else 
								{ ?>
									<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' /><?php 
								} 

								// show QA icon
								if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read')) 
								{ ?>
									<a target='_blank' href='<?php echo $this->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$aQuestion['gid']}/qid/{$aQuestion['qid']}/"); ?>'><img src='<?php echo $sImageURL; ?>org_qaok_20.png' alt='<?php $clang->eT("Check survey logic for current question"); ?>' /></a><?php 
								} 
								else 
								{ ?>
									<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' /><?php 
								} 

								// get question attributes to prepare hidden and mandatory icon view and show them
								$aThisQuestionAttributes=QuestionAttribute::model()->getQuestionAttributes($aQuestion['qid']);?>

								<a href=''><img src='<?php echo $sImageURL; ?><?php 
								if($aThisQuestionAttributes['hidden']==0) { ?>org_visible_20.png' alt='<?php $clang->eT("Always hide this question"); ?>: <?php $clang->eT("No"); ?>' <?php } else { ?>org_hidden_20.png' alt='<?php $clang->eT("Always hide this question"); ?>: <?php $clang->eT("Yes"); ?>' <?php } ?> /></a>

								<a href=''><img src='<?php echo $sImageURL; ?><?php if($aQuestion['mandatory']=='N') { ?>org_mandatoryno_20.png' alt='<?php $clang->eT("Mandatory:"); ?> <?php $clang->eT("No"); ?>' <?php } else { ?>org_mandatory_20.png' alt='<?php $clang->eT("Mandatory:"); ?> <?php $clang->eT("Yes"); ?>'<?php } ?> /></a>

								<?php 
								// show copy-question & add question icons
								if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','create')) 
								{ ?>
									<a href='<?php echo $this->createUrl("admin/questions/sa/copyquestion/surveyid/" . $surveyid . "/gid/".$aQuestion['gid']."/qid/".$aQuestion['qid']);?>'><img src='<?php echo $sImageURL; ?>org_copy_20.png'  alt='<?php $clang->eT("Copy Current Question"); ?>' /></a>
									<a href='<?php echo $this->createUrl("admin/questions/sa/addquestion/surveyid/".$surveyid."/gid/".$aQuestion['gid']); ?>'><img src='<?php echo $sImageURL; ?>org_add_20.png'  alt='<?php $clang->eT("Add new question to group"); ?>' /></a><?php 
								} else 
								{ ?>
									<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' />
									<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' /><?php 
								} 

								// show export-question icon
								if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','export')) 
								{ ?>
									<a href='<?php echo $this->createUrl("admin/export/sa/question/surveyid/".$surveyid."/gid/".$aQuestion['gid']."/qid/".$aQuestion['qid']);?>'><img src='<?php echo $sImageURL; ?>org_dumpquestion_20.png' alt='<?php $clang->eT("Export this question"); ?>' /></a><?php 
								} 
								else 
								{ ?>
									<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' /><?php 
								} 

								// show delete-question icon
								if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','delete')) 
								{ ?>
									<a href='#' onclick="if (confirm('<?php $clang->eT("Deleting this question will also delete any answer options and subquestions it includes. Are you sure you want to continue?","js"); ?>')) { <?php echo convertGETtoPOST($this->createUrl('admin/questions/sa/delete/surveyid/'.$surveyid.'/gid/'.$aQuestion['gid'].'/qid/'.$aQuestion['qid'])); ?>}"><img src='<?php echo $sImageURL; ?>org_delete_20.png'  alt='<?php $clang->eT("Delete current question"); ?>' /></a><?php 
								} 
									else 
								{ ?>
									<img src='<?php echo $sImageURL; ?>org_emptyicon_20.png' /><?php
								} ?>
								</div>
							</div>
							<div class='gq-infocol'>
								<div class='gq-infotext'>
									<img src='<?php echo $sImageURL; ?>org_more.png' align='right' id='list_q<?php echo $aQuestion['qid'];?>' style="display:none;"/>
									<b><a href='<?php echo Yii::app()->getController()->createUrl('admin/questions/sa/editquestion/surveyid/'.$surveyid.'/gid/'.$aQuestion['gid'].'/qid/'.$aQuestion['qid']);?>'><?php echo $aQuestion['title'];?></a></b>: <?php echo flattenText($aQuestion['question'],true);?>
								</div>
							</div>
						</div>
						
				</li><?php
			}?>

			</ol><?php
		}?>

		</li><?php 
	}?>

	</ol>

	<?php 
	if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')) 
	{ ?>
		<div class='organizeFooter'>
			<div>
				<div class="actions"><a href="#" data-select="all"><?php $clang->eT("Select all"); ?></a> / </div>
				<div class="actions"><a href="#" data-select="none"><?php $clang->eT("Unselect all"); ?></a> / </div> 
				<div class="actions"><a href="#" data-select="toggle"><?php $clang->eT("Toggle selection"); ?></a> </div>
				<div class="conclude">&nbsp;</div>
				<div class="actions"><?php $clang->eT("Set selected:"); ?> </div>
		
				<select id="bulkaction" name="" size="1">
					<option>...</option>
					<option value="asvisible"><?php $clang->eT("as visible"); ?></option>
					<option value="ashidden"><?php $clang->eT("as always hidden"); ?></option>
					<option value="asmandatory"><?php $clang->eT("as mandatory"); ?></option>
					<option value="asoptional"><?php $clang->eT("as optional"); ?></option>
				</select>
			</div>
			<br /><br />
		</div><?php 
	} ?>
</div>

<?php echo CHtml::form(array("admin/survey/sa/organize/surveyid/{$surveyid}"), 'post', array('id'=>'frmOrganize')); ?>
    <p>
        <input type='hidden' id='orgdata' name='orgdata' value='' />
        <button id='btnSave'><?php echo $clang->eT('Save'); ?></button>
    </p>
</form>
