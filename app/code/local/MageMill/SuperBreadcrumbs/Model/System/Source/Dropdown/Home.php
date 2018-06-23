<?php
/*
 * Copyright Mtrgovina, (c) 2014
 *
 * @category   Mtrgovina Eracuni Import/Export
 * @package    Mtrgovina_Eracuni
 * @copyright  Copyright (c) 2014 Mtrgovina; http://www.mtrgovina.com
*/

class MageMill_SuperBreadcrumbs_Model_System_Source_Dropdown_Home
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'no',
                'label' => 'Do not display',
            ),
            array(
                'value' => 'text',
                'label' => 'Display as text',
            ),
			array(
                'value' => 'link',
                'label' => 'Display as link',
            )
        );
    }
}