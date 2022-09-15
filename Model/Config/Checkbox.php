<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_DemoModule
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Culqi\Pago\Model\Config;

/**
 * Used in creating options for getting product type value
 *
 */
class Checkbox
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'tarjeta', 'label' => __('Tarjetas débito/credito')], 
            ['value' => 'bancamovil', 'label' => __('Banca móvil o internet')],
            ['value' => 'yape', 'label' => __('Yape')],
            ['value' => 'agente', 'label' => __('Agentes y bodegas')],
            ['value' => 'billetera', 'label' => __('Billeteras móviles')],
            ['value' => 'cuotealo', 'label' => __('Cuotéalo BCP')],
        ];
    }
}