<?php

namespace Yaoi\Cli\Command;

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
            if ($argument->type === Command\Option::TYPE_ENUM) {
                foreach ($argument->values as $value) {
                    $arguments[$index][]= '"' . $value . '"';
                }
            }
        }

?>
#!/bin/bash

# Bash Completion for <?=$def->description?>

_<?=$def->name?>() {
    COMPREPLY=()
    local self=${COMP_WORDS[0]}
    local cur=${COMP_WORDS[COMP_CWORD]}
    local prev=${COMP_WORDS[COMP_CWORD-1]}
    local options=(<?php
        if ($prep->byShortName) {
            echo '"-' . implode('" "-', array_keys($prep->byShortName)) . '" ';
        }
        if ($prep->byName) {
            echo '"--' . implode('" "--', array_keys($prep->byName)) . '" ';
        }
        ?>)
    local arguments=()
    local arrayOptions=()

<?php
foreach ($arguments as $index => $values) {
?>
    if [ $COMP_CWORD = <?=$index + 1?> ]; then
        commands=(<?=implode(' ', $values)?>)
        COMPREPLY=( $( compgen -W "${commands[*]}" -- $cur) )
        return 0
    fi
<?php
}
?>

    if [ $COMP_CWORD -eq 1 ]; then
        if [[ ${cur} == -* ]] ; then
            COMPREPLY=( $( compgen -W "${options[*]}" -- $cur) )
        else
            commands=("help" "list" "delete" "diff" "get" "say")
            COMPREPLY=( $( compgen -W "${commands[*]}" -- $cur) )
        fi
        return 0
    fi

    filteredOptions=()
    for opt in "${options[@]}"; do
        skip=
        for word in "${COMP_WORDS[@]}"; do
            if [[ $opt == $word ]]; then
                skip=1
                for optArr in "${arrayOptions[@]}"; do
                    [[ $opt == $optArr ]] && { skip=; break; }
                done
                [[ -n $skip ]] && break;
            fi
        done
        [[ -n $skip ]] || filteredOptions+=("$opt");
    done

    COMPREPLY=( $( compgen -W "${filteredOptions[*]}" -- $cur) )
}

complete -F _<?=$def->name?> <?=$def->name?>

<?php
}

}