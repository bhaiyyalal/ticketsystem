<?php
/* @var $this UserController */
/* @var $model User */

$this->pageTitle = Yii::app()->name . ' |  Add Template' ;
?>

<!-- begin PAGE TITLE AREA -->
<div class="row">
    <div class="col-lg-12">
        <div class="page-title">            
            <ol class="breadcrumb">
                <li><h1><i class="fa fa-user"></i> Manage Template | <small><a class="text-blue" href="<?php echo Yii::app()->createAbsoluteUrl('template/index'); ?>">Back to Listing</a></small></h1></li>
            </ol>
        </div>
    </div>    
</div>
<!-- end PAGE TITLE AREA -->

<div class="row">
    <div class="col-lg-12">

        <div class="portlet portlet-default">
            <div class="portlet-body">
                <ul id="userTab" class="nav nav-tabs">
                    <li class="active"><a href="#template-add" data-toggle="tab">Add Template</a></li>
                </ul>
                <div id="userTabContent" class="tab-content">
                    <div class="tab-pane fade active in" id="template-add">                            
                        <div class="row">
                            <div class="col-md-12">

                                <?php $this->renderPartial('_form', array('model' => $model)); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </div>    
</div>