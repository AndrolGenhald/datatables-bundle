<?php

/*
 * Symfony DataTables Bundle
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Omines\DataTablesBundle\Filter;

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFilter
{
    /**
     * @psalm-suppress PropertyNotSetInConstructor
     *
     * @var string
     */
    protected $template_html;

    /**
     * @psalm-suppress PropertyNotSetInConstructor
     *
     * @var string
     */
    protected $operator;

    public function __construct(?array $options = null)
    {
        $this->set($options ?? []);
    }

    final public function set(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        foreach ($resolver->resolve($options) as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @return $this
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'template_html' => null,
            'operator' => 'CONTAINS',
        ]);

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateHtml()
    {
        return $this->template_html;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    public function getValue(string $value): ?string
    {
        return $value;
    }
}
