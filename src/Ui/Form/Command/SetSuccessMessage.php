<?php namespace Anomaly\Streams\Platform\Ui\Form\Command;

use Anomaly\Streams\Platform\Message\MessageBag;
use Anomaly\Streams\Platform\Ui\Form\FormBuilder;
use Illuminate\Contracts\Bus\SelfHandling;

/**
 * Class SetSuccessMessage
 *
 * @link          http://anomaly.is/streams-platform
 * @author        AnomalyLabs, Inc. <hello@anomaly.is>
 * @author        Ryan Thompson <ryan@anomaly.is>
 * @package       Anomaly\Streams\Platform\Ui\Form\Command
 */
class SetSuccessMessage implements SelfHandling
{

    /**
     * The form builder.
     *
     * @var FormBuilder
     */
    protected $builder;

    /**
     * Create a new SetSuccessMessage instance.
     *
     * @param FormBuilder $builder
     */
    public function __construct(FormBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Handle the command.
     */
    public function handle(MessageBag $messages)
    {
        // If we can't save or there are errors then skip it.
        if ($this->builder->getFormErrors() || !$this->builder->canSave()) {
            return;
        }

        $mode = $this->builder->getFormMode();

        // False means no message is desired.
        if ($this->builder->getFormOption('success_message') === false) {
            return;
        }

        $entry  = $this->builder->getFormEntry();
        $stream = $this->builder->getFormStream();

        $parameters = [
            'title' => $entry ? $entry->getTitle() : null,
            'name'  => $stream ? $stream->getName() : null
        ];

        // If the name doesn't exist we need to be clever.
        if (str_contains($parameters['name'], '::') && !trans()->has($parameters['name']) && $stream) {
            $parameters['name'] = ucfirst(str_singular(str_replace('_', ' ', $stream->getSlug())));
        } else {
            $parameters['name'] = str_singular(trans($parameters['name']));
        }

        /**
         * Use the option success message.
         */
        if ($this->builder->getFormOption('success_message') !== null) {
            $this->builder->setFormOption(
                'success_message',
                trans('streams::message.' . $mode . '_success', $parameters)
            );
        }

        /**
         * Set the default success message.
         */
        if ($this->builder->getFormOption('success_message') === null) {
            $this->builder->setFormOption(
                'success_message',
                trans('streams::message.' . $mode . '_success', $parameters)
            );
        }

        $messages->success($this->builder->getFormOption('success_message'));
    }
}
