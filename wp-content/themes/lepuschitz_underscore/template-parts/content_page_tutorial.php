<?php

class Studnet {
    public $name;
    public $lastName;
    public $espb;
    public $avg;

    public function Budget (){
        if ($this->espb >= 85){
            return true;
        }else {
            return false;
        }
    }
}
$Janko = new Studnet();
$Janko->name = "Janko";
$Janko->lastName = "Jankovic";
$Janko->espb = 91;
$Janko->avg = 9;
?>

<div>
    <p class="text text-center">
        Hello! My name is <?php echo $Janko->name?>
    </p>
    <p class="text text-center">
        My average grade is <?php echo $Janko->avg ?>
    </p>

    <?php if ($Janko->Budget()): ?>

        <p class="text text-center">
            I am lucky not to be paying for my studies!
        </p>

    <?php else: ?>

        <p class="text text-center">
            Unfortunately, my studies are very expensive :(
        </p>

    <?php endif;?>
</div>
