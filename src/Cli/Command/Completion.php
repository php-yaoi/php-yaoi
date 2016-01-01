<?php

namespace Yaoi\Cli\Command;

use Yaoi\Cli\Option;
use Yaoi\Command;
use Yaoi\View\Hardcoded;

class Completion extends Hardcoded
{
    protected $command;

    public function __construct(Command $command) {
        $this->command = $command;
    }

    protected function renderDefinition(Command\Definition $definition, $argumentsShift = 1)
    {
        $prep = new PrepareDefinition((array)$definition->options);
        $arguments = array(
            $argumentsShift => '',
        );


        foreach ($prep->requiredArguments as $index => $argument) {
            $index += $argumentsShift;

            if ($argument->type === Option::TYPE_ENUM) {
                $arguments[$index] = '';
                foreach ($argument->values as $value) {
                    $arguments[$index] .= '"' . $value . '" ';
                }
            } else/*if ($argument->type === Option::TYPE_VALUE)*/ {
                $arguments[$index] = '';
            }
        }
        //var_dump($definition->name, $arguments);

        $optionsStrings = array(
            Runner::GROUP_MISC => '',
            Runner::GROUP_DEFAULT => '',
        );

        $optionsValues = array();

        foreach ($prep->optionsArray as $option) {
            if ($option instanceof Option && $option->isUnnamed) {
                continue;
            }

            $group = $option->group === Runner::GROUP_MISC
                ? Runner::GROUP_MISC
                : Runner::GROUP_DEFAULT;

            $optionsStrings [$group] .= '"' . Runner::OPTION_NAME . $option->getName() . '" ';

            if ($option->type === Option::TYPE_VALUE) {
                $optionsValues [Runner::OPTION_NAME . $option->getName()] = '';
            } elseif ($option->type === Option::TYPE_ENUM) {
                $optionsValues [Runner::OPTION_NAME . $option->getName()] = '"' . implode('" "', $option->values) . '" ';
            }
        }

?>
for opt in <?php echo $optionsStrings[Runner::GROUP_MISC] ?>; do
    if [[ $opt == $prev ]]; then
        return 0
    fi
done

<?php foreach ($arguments as $index => $values) { ?>
if [ $COMP_CWORD = <?= $index ?> ]; then
    options=(<?php echo $values ?>)
fi
<?php } ?>

if [ $COMP_CWORD = <?php echo $argumentsShift ?> ]; then
    if [[ ${cur} == -* ]] ; then
        options+=(<?php echo $optionsStrings[Runner::GROUP_MISC] ?>)
    fi
fi

<?php if ($optionsStrings[Runner::GROUP_DEFAULT]) { ?>
for opt in <?php echo $optionsStrings[Runner::GROUP_DEFAULT] ?>; do
    skip=
    for word in "${COMP_WORDS[@]}"; do
        if [[ $opt == $word ]]; then
            skip=1
            break;
        fi
    done
    [[ -n $skip ]] || options+=("$opt");
done

<?php
    }
        if ($optionsValues) { ?>
case "$prev" in
<?php foreach ($optionsValues as $name => $values) { ?>
    "<?php echo $name ?>")
    options=(<?php echo $values; ?>)
    ;;
<?php } ?>
esac
<?php }
    }


    public function render()
    {
        $def = $this->command->definition();
?>
#!/bin/bash

# Bash Completion for <?=$def->description?>

_<?= $def->name ?>() {
COMPREPLY=()
local self=${COMP_WORDS[0]}
local first=${COMP_WORDS[1]}
local cur=${COMP_WORDS[COMP_CWORD]}
local prev=${COMP_WORDS[COMP_CWORD-1]}
local options=()

<?php $this->renderDefinition($def); ?>

if [[ ${cur} == -* ]] ; then
    COMPREPLY=( $( compgen -W "${options[*]}" -- $cur) )
    return 0
fi

COMPREPLY=( $( compgen -W "${options[*]}" -- $cur) )
}

complete -F _<?= $def->name ?> <?= $def->name ?>

<?php
}

}