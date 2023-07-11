<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var string $memoryLimit
 * @var string $minMemoryLimit
 * @var bool $isOk
 */
?>
<p>
<div class="sub-title">STATUS</div>
<p>
    <?php if ($isOk) : ?>
        <i class='green'>
            The memory_limit has a value of <b>[<?php echo $memoryLimit; ?>]</b> which is higher or equal to the suggested minimum of 
            <b>[<?php echo $minMemoryLimit; ?>]</b>.
        </i>
    <?php else : ?>
        <i class='red'>
            The memory_limit has a value of <b>[<?php echo $memoryLimit; ?>]</b> 
            which is lower than the suggested minimum of <b>[<?php echo $minMemoryLimit; ?>]</b>.
        </i>
    <?php endif; ?>
</p>

<div class="sub-title">DETAILS</div>
<p>

</p>
The 'memory_limit' configuration in php.ini sets how much memory a script can use during its runtime. 
When this value is lower than the suggested minimum of
<?php echo $minMemoryLimit; ?> the installer might run into issues.

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        Try Increasing the memory_limit.&nbsp;
        <a href="https://snapcreek.com/duplicator/docs/faqs-tech/?210328131212#faq-trouble-056-q" target="_blank">[Additional FAQ Help]</a>
    </li>
</ul>