<?php

namespace Yaoi\Cli\Command;

use Yaoi\Cli\Option;
use Yaoi\Command;
use Yaoi\View\Hardcoded;

class Completion extends Hardcoded
{
    private $command;

    public function __construct(Command $command) {
        $this->command = $command;
    }

    public function render()
    {
        $def = $this->command->definition();
        $prep = new PrepareDefinition($this->command->optionsArray());

        //var_dump(array_keys($prep->requiredArguments));
        //exit;

        $arguments = array();

        foreach ($prep->requiredArguments as $index => $argument) {
            if ($argument->type === Option::TYPE_ENUM) {
                $arguments[$index] = '';
                foreach ($argument->values as $value) {
                    $arguments[$index] .= '"' . $value . '" ';
                }
            }
            elseif ($argument->type === Option::TYPE_VALUE) {
                $arguments[$index] = '';
            }
        }

        $optionsStrings = array(
            Runner::GROUP_MISC => '',
            Runner::GROUP_DEFAULT => '',
        );

        $valuedOptions = array();

        foreach ($prep->optionsArray as $option) {
            if ($option instanceof Option && $option->isUnnamed) {
                continue;
            }

            $group = $option->group === Runner::GROUP_MISC
                ? Runner::GROUP_MISC
                : Runner::GROUP_DEFAULT;

            $optionsStrings [$group] .= '"' . Runner::OPTION_NAME . $option->getName() . '" ';
            /*
            if ($option->shortName) {
                $optionsStrings [$group] .= '"' . Runner::OPTION_SHORT . $option->shortName . '" ';
            }
            */

            if ($option->type === Option::TYPE_VALUE) {
                $valuedOptions [Runner::OPTION_NAME . $option->getName()] = '';
            }
            elseif ($option->type === Option::TYPE_ENUM) {
                $valuedOptions [Runner::OPTION_NAME . $option->getName()] = '"' . implode('" "', $option->values) . '" ';
            }
        }

        $arguments[0] .= $optionsStrings[Runner::GROUP_MISC];

?>
#!/bin/bash

# Bash Completion for <?=$def->description?>

_<?=$def->name?>() {
    COMPREPLY=()
    local self=${COMP_WORDS[0]}
    local first=${COMP_WORDS[1]}
    local cur=${COMP_WORDS[COMP_CWORD]}
    local prev=${COMP_WORDS[COMP_CWORD-1]}
    local available_options=(<?php echo $optionsStrings[Runner::GROUP_DEFAULT] ?>)
    local options=()

    for opt in "${available_options[@]}"; do
        skip=
        for word in "${COMP_WORDS[@]}"; do
            if [[ $opt == $word ]]; then
                skip=1
                break;
            fi
        done
        [[ -n $skip ]] || options+=("$opt");
    done


    for opt in <?= $optionsStrings[Runner::GROUP_MISC]?>; do
        if [[ $opt == $first ]]; then
            return 0
        fi
    done

<?php foreach ($arguments as $index => $values) { ?>
    if [ $COMP_CWORD = <?=$index + 1?> ]; then
        options=(<?php echo $values ?>)
    fi
<?php } ?>

    case "$prev" in
<?php foreach ($valuedOptions as $name => $values) { ?>
        "<?php echo $name?>")
            options=(<?php echo $values;?>)
        ;;
<?php } ?>
    esac


    if [[ ${cur} == -* ]] ; then
        COMPREPLY=( $( compgen -W "${options[*]}" -- $cur) )
        return 0
    fi

    COMPREPLY=( $( compgen -W "${options[*]}" -- $cur) )
}

complete -F _<?=$def->name?> <?=$def->name?>

<?php
}

}