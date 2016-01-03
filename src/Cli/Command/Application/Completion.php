<?php

namespace Yaoi\Cli\Command\Application;

use Yaoi\Command\Application;

class Completion extends \Yaoi\Cli\Command\Completion
{
    /** @var  Application */
    protected $command;

    public function __construct(Application $command)
    {
        parent::__construct($command);
    }



    public function render()
    {
        $def = $this->command->definition();

        ?>
#!/bin/bash

# Bash Completion for <?php echo $def->description ?>

_<?php echo $def->name ?>() {
COMPREPLY=()
local self=${COMP_WORDS[0]}
local action=${COMP_WORDS[1]}
local cur=${COMP_WORDS[COMP_CWORD]}
local prev=${COMP_WORDS[COMP_CWORD-1]}
local options=()

<?php $this->renderDefinition($def); ?>

case "$action" in
<?php foreach ($def->actions as $name => $commandDefinition) { ?>
    "<?php echo $name ?>")
    <?php $this->renderDefinition($commandDefinition, 2); ?>
    ;;
<?php } ?>
esac


if [[ ${cur} == -* ]] ; then
    COMPREPLY=( $( compgen -W "${options[*]}" -- $cur) )
    return 0
fi

COMPREPLY=( $( compgen -W "${options[*]}" -- $cur) )
}

complete -F _<?php echo $def->name ?> -o default <?php echo $def->name ?>
<?php
    }
}