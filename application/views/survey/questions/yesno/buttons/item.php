<?php
/**
 * Yes / No Question, buttons item Html
 * 
 * @var $name                           $ia[1]
 * @var $yChecked
 * @var $nChecked
 * @var $naChecked
 * @var $noAnswer
 * @var $checkconditionFunction         $checkconditionFunction(this.value, this.name, this.type)
 * @var $value                          $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$name]
 */
?>

<div class="list-unstyled answers-list radio-list">
      <div class="btn-group" data-toggle="buttons" id="<?php echo $name;?>-container">

        <!-- Yes -->
        <label class="btn btn-primary btn-lg <?php if($yChecked){ echo "active";}?> ">
          <input
              class="radio"
              type="radio"
              name="<?php echo $name;?>"
              id="answer<?php echo $name;?>Y"
              value="Y"
              <?php echo $yChecked; ?>
              onclick="<?php echo $checkconditionFunction; ?>"
          />
          <?php eT('Yes');?>
        </label>

        <!-- No -->
        <label class="btn btn-primary  btn-lg <?php if($nChecked){ echo "active";}?> ">
            <input
                class="radio"
                type="radio"
                name="<?php echo $name;?>"
                id="answer<?php echo $name;?>N"
                value="N"
                <?php echo $nChecked; ?>
                onclick="<?php echo $checkconditionFunction;?>"
            />
            <?php eT('No');?>
        </label>

        <!-- No answer -->
        <label class="btn btn-primary btn-lg  <?php if($naChecked){ echo "active";}?>">

            <input
                class="radio"
                type="radio"
                name="<?php echo $name;?>"
                id="answer<?php echo $name;?>"
                value=""
                <?php echo $naChecked; ?>
                onclick="<?php echo $checkconditionFunction;?>"
            />
                <?php eT('No answer');?>

        </label>
      </div>

      <input
          type="hidden"
          name="java<?php echo $name;?>"
          id="java<?php echo $name;?>"
          value="<?php echo $value;?>"
      />

</div>

<script>
$(document).on('change', 'div#<?php echo $name;?>-container input:radio', function (event) {
    checkconditions(this.value, this.name, this.type);
});
</script>
