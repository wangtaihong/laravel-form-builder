<?php  namespace Kris\LaravelFormBuilder\Fields;

use Kris\LaravelFormBuilder\Form;

abstract class FormField
{
    /**
     * @var
     */
    protected $name;

    /**
     * @var
     */
    protected $type;

    /**
     * @var
     */
    protected $options;

    /**
     * @var bool
     */
    protected $rendered = false;

    /**
     * @var Form
     */
    protected $parent;

    /**
     * @var string
     */
    protected $template;

    /**
     * Array of valid types for single type
     *
     * @var array
     */
    protected $validFieldTypes = [];

    /**
     * @param             $name
     * @param             $type
     * @param Form        $parent
     * @param array       $options
     */
    public function __construct($name, $type, Form $parent, array $options = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->parent = $parent;
        $this->setTemplate();
        $this->setDefaultOptions($options);
    }

    /**
     * Get the template, can be config variable or view path
     *
     * @return string
     */
    abstract protected function getTemplate();

    /**
     * @param array $options
     * @param bool  $showLabel
     * @param bool  $showField
     * @param bool  $showError
     * @return string
     */
    public function render(array $options = [], $showLabel = true, $showField = true, $showError = true)
    {
        $this->rendered = true;

        $options = $this->prepareOptions($options);

        return $this->parent->getFormHelper()->getView()->make(
            $this->template, [
                'name' => $this->name,
                'type' => $this->type,
                'options' => $options,
                'showLabel' => $showLabel,
                'showField' => $showField,
                'showError' => $showError
            ])->render();
    }

    /**
     * @param array $options
     * @return array
     */
    protected function prepareOptions(array $options = [])
    {
        $formHelper = $this->parent->getFormHelper();

        $options = $formHelper->mergeOptions($this->options, $options);
        $options['wrapperAttrs'] = $formHelper->prepareAttributes($options['wrapper']);
        $options['errorAttrs'] = $formHelper->prepareAttributes($options['errors']);

        return $options;
    }

    /**
     * Get name of the field
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get field options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set field options
     *
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $this->prepareOptions($options);

        return $this;
    }

    /**
     * Get the type of the field
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type of the field
     *
     * @param mixed $type
     * @return $this
     */
    public function setType($type)
    {
        if ($this->parent->getFormHelper()->getFieldType($type)) {
            $this->type = $type;
        }

        return $this;
    }

    /**
     * Check if the field is rendered
     *
     * @return bool
     */
    public function isRendered()
    {
        return $this->rendered;
    }

    /**
     * Default options for field
     *
     * @return array
     */
    protected function getDefaults()
    {
        return [];
    }

    /**
     * Defaults used across all fields
     *
     * @return array
     */
    private function allDefaults()
    {
        $config = $this->parent->getFormHelper()->getConfig();

        return [
            'wrapper' => ['class' => $config->get('laravel-form-builder::defaults.wrapper_class')],
            'attr' => ['class' => $config->get('laravel-form-builder::defaults.field_class')],
            'default_value' => null,
            'label' => $this->name,
            'label_attr' => [],
            'errors' => ['class' => $config->get('laravel-form-builder::defaults.error_class')]
        ];
    }

    /**
     * @param array $options
     */
    protected function setDefaultOptions(array $options = [])
    {
        $formHelper = $this->parent->getFormHelper();

        $this->options = $formHelper->mergeOptions($this->allDefaults(), $this->getDefaults());
        $this->options = $this->prepareOptions($options);

        if (isset($this->options['template']) && $this->options['template'] !== null) {
            $this->template = $this->options['template'];
            unset($this->options['template']);
        }
    }

    /**
     * Set the template property on the object
     */
    private function setTemplate()
    {
        $this->template = $this->parent->getFormHelper()
            ->getConfig()->get($this->getTemplate(), $this->getTemplate());
    }
}