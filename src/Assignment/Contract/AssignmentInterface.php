<?php namespace Anomaly\Streams\Platform\Assignment\Contract;

use Anomaly\Streams\Platform\Addon\FieldType\FieldType;
use Anomaly\Streams\Platform\Field\Contract\FieldInterface;
use Anomaly\Streams\Platform\Stream\Contract\StreamInterface;

/**
 * Interface AssignmentInterface
 *
 * @link    http://anomaly.is/streams-platform
 * @author  AnomalyLabs, Inc. <hello@anomaly.is>
 * @author  Ryan Thompson <ryan@anomaly.is>
 * @package Anomaly\Streams\Platform\Assignment\Contract
 */
interface AssignmentInterface
{

    /**
     * Get the related stream.
     *
     * @return StreamInterface
     */
    public function getStream();

    /**
     * Get the related field.
     *
     * @return FieldInterface
     */
    public function getField();

    /**
     * Get the label.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Get the instructions.
     *
     * @return string
     */
    public function getInstructions();

    /**
     * Get the placeholder.
     *
     * @return string
     */
    public function getPlaceholder();

    /**
     * Get the unique flag.
     *
     * @return bool
     */
    public function isUnique();

    /**
     * Get the required flag.
     *
     * @return bool
     */
    public function isRequired();

    /**
     * Get the translatable flag.
     *
     * @return bool
     */
    public function isTranslatable();

    /**
     * Get the field slug.
     *
     * @return mixed
     */
    public function getFieldSlug();

    /**
     * Get the assignment's field's type.
     *
     * @return FieldType
     */
    public function getFieldType();

    /**
     * Get the assignment's field's name.
     *
     * @param null $locale
     * @return string
     */
    public function getFieldName();

    /**
     * Get the column name.
     *
     * @return mixed
     */
    public function getColumnName();

    /**
     * Get all attributes.
     *
     * @return mixed
     */
    public function getAttributes();

    /**
     * Get an attribute.
     *
     * @param  $key
     * @return mixed
     */
    public function getAttribute($key);
}
