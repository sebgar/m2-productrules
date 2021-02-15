<?php
namespace Sga\ProductRules\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    protected $_scopeConfig;

    protected $_columnAffectedData;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;

        parent::__construct($context);
    }

    public function getConditionsForCombine()
    {
        return $this->_getInfoRules('conditions');
    }

    public function getActionsForCollection()
    {
        return $this->_getInfoRules('actions');
    }

    public function getColumnAffectedData()
    {
        return $this->_columnAffectedData;
    }

    public function setColumnAffectedData($v)
    {
        return $this->_columnAffectedData = $v;
    }

    protected function _getInfoRules($namespace = '')
    {
        $data = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $lines = $this->_scopeConfig->get('system', 'default/productrules/'.$namespace);
        if (is_array($lines)) {
            foreach ($lines as $child) {
                if (!isset($child['title'])) {
                    // first level
                    if (isset($child['class']) && $child['class'] !== '' && isset($child['method']) && $child['method'] !== '') {
                        $model = $objectManager->create($child['class']);
                        $l = $model->{$child['method']}();
                        foreach ($l as $i) {
                            $data[] = $i;
                        }
                    } else {
                        $data[] = array(
                            'label' => (isset($child['label']) ? __($child['label']) : ''),
                            'value' => (isset($child['object']) ? $child['object'] : '')
                        );
                    }
                } else {
                    // second level
                    $title = __($child['title']);

                    $children = $child;
                    unset($children['title']);

                    $tmp = array();
                    foreach ($children as $child2) {
                        if (isset($child2['class']) && $child2['class'] != '' && isset($child2['method']) && $child2['method'] != '') {
                            $model = $objectManager->create($child2['class']);
                            $l = $model->{$child2['method']}();
                            foreach ($l as $i) {
                                $tmp[] = $i;
                            }
                        } else {
                            $tmp[] = array(
                                'label' => (isset($child2['label']) ? __($child2['label']) : ''),
                                'value' => (isset($child2['object']) ? $child2['object'] : '')
                            );
                        }
                    }

                    $data[] = array(
                        'label' => $title,
                        'value' => $tmp
                    );
                }
            }
        }

        return $data;
    }

    public function convertFiltersClause($filters)
    {
        $conditions = array();
        if (is_array($filters)) {
            if (isset($filters['operator']) && isset($filters['list'])) {
                $f = $this->convertFiltersClause($filters['list']);
                if (!empty($f)) {
                    $conditions[] = '('.implode(' '.$filters['operator'].' ', $f).')';
                }
            } else {
                foreach ($filters as $filter) {
                    if (isset($filter['operator']) && isset($filter['list'])) {
                        $f = $this->convertFiltersClause($filter['list']);
                        if (!empty($f)) {
                            $conditions[] = '('.implode(' '.$filter['operator'].' ', $f).')';
                        }
                    } elseif (is_array($filter)) {
                        $f = '';
                        foreach ($filter as $filterKey => $cond) {
                            foreach ($cond as $condKey => $values) {
                                switch(strtolower($condKey)) {
                                    case 'in':
                                        if (is_array($values)) {
                                            $t = array();
                                            foreach ($values as $value) {
                                                if (is_string($value)) {
                                                    $t[] = '"'.$value.'"';
                                                } else {
                                                    $t[] = $value;
                                                }
                                            }
                                            $f .= $filterKey.' IN ('.implode(',', $t).')';
                                        } elseif (is_string($values)) {
                                            $f .= $filterKey.' IN ("'.$values.'")';
                                        } else {
                                            $f .= $filterKey.' IN ('.$values.')';
                                        }
                                        break;

                                    case 'nin':
                                        if (is_array($values)) {
                                            $t = array();
                                            foreach ($values as $value) {
                                                if (is_string($value)) {
                                                    $t[] = '"'.$value.'"';
                                                } else {
                                                    $t[] = $value;
                                                }
                                            }
                                            $f .= $filterKey.' NOT IN ('.implode(',', $t).')';
                                        } elseif (is_string($values)) {
                                            $f .= $filterKey.' NOT IN ("'.$values.'")';
                                        } else {
                                            $f .= $filterKey.' NOT IN ('.$values.')';
                                        }
                                        $f .=  ' OR ' . $filterKey . ' IS NULL';
                                        break;

                                    case 'like':
                                        if (is_array($values)) {
                                            $t = array();
                                            foreach ($values as $value) {
                                                if (is_string($value)) {
                                                    $t[] = $filterKey.' LIKE "'.$value.'"';
                                                } else {
                                                    $t[] = $filterKey.' LIKE '.$values;
                                                }
                                            }
                                            $f .= implode(' OR ', $t);
                                        } elseif (is_string($values)) {
                                            $f .= $filterKey.' LIKE "'.$values.'"';
                                        } else {
                                            $f .= $filterKey.' LIKE '.$values;
                                        }
                                        break;

                                    case 'nlike':
                                        if (is_array($values)) {
                                            $t = array();
                                            foreach ($values as $value) {
                                                if (is_string($value)) {
                                                    $t[] = $filterKey.' NOT LIKE "'.$value.'"';
                                                } else {
                                                    $t[] = $filterKey.' NOT LIKE '.$values;
                                                }
                                            }
                                            $f .= implode(' AND ', $t);
                                        } elseif (is_string($values)) {
                                            $f .= $filterKey.' NOT LIKE "'.$values.'"';
                                        } else {
                                            $f .= $filterKey.' NOT LIKE '.$values;
                                        }
                                        break;

                                    case 'finset':
                                        if (is_array($values)) {
                                            $t = array();
                                            foreach ($values as $value) {
                                                if (is_string($value)) {
                                                    $t[] = 'FIND_IN_SET("'.$value.'", '.$filterKey.') > 0';
                                                } else {
                                                    $t[] = 'FIND_IN_SET('.$value.', '.$filterKey.') > 0';
                                                }
                                            }
                                            $f .= implode(' OR ', $t);
                                        } elseif (is_string($values)) {
                                            $f .= 'FIND_IN_SET("'.$values.'", '.$filterKey.') > 0';
                                        } else {
                                            $f .= 'FIND_IN_SET('.$values.', '.$filterKey.') > 0';
                                        }
                                        break;

                                    case 'nfinset':
                                        if (is_array($values)) {
                                            $t = array();
                                            foreach ($values as $value) {
                                                if (is_string($value)) {
                                                    $t[] = 'FIND_IN_SET("'.$value.'", '.$filterKey.') = 0';
                                                } else {
                                                    $t[] = 'FIND_IN_SET('.$value.', '.$filterKey.') = 0';
                                                }
                                            }
                                            $f .= implode(' AND ', $t);
                                        } elseif (is_string($values)) {
                                            $f .= 'FIND_IN_SET("'.$values.'", '.$filterKey.') = 0';
                                        } else {
                                            $f .= 'FIND_IN_SET('.$values.', '.$filterKey.') = 0';
                                        }
                                        break;

                                    case '==':
                                        if (is_array($values)) {
                                            $t = array();
                                            foreach ($values as $value) {
                                                if (is_string($value)) {
                                                    $t[] = $filterKey.' = "'.$value.'"';
                                                } else {
                                                    $t[] = $filterKey.' = '.$values;
                                                }
                                            }
                                            $f .= implode(' OR ', $t);
                                        } elseif (is_string($values)) {
                                            $f .= $filterKey.' = "'.$values.'"';
                                        } else {
                                            $f .= $filterKey.' = '.$values;
                                        }
                                        break;

                                    case '!=':
                                        if (is_array($values)) {
                                            $t = array();
                                            foreach ($values as $value) {
                                                if (is_string($value)) {
                                                    $t[] = $filterKey.' != "'.$value.'"';
                                                } else {
                                                    $t[] = $filterKey.' != '.$values;
                                                }
                                            }
                                            $f .= implode(' AND ', $t);
                                        } elseif (is_string($values)) {
                                            $f .= $filterKey.' != "'.$values.'"';
                                        } else {
                                            $f .= $filterKey.' != '.$values;
                                        }
                                        break;

                                    case 'isnull':
                                        $f .= $filterKey.' IS NULL';
                                        break;

                                    case 'isnotnull':
                                        $f .= $filterKey.' IS NOT NULL';
                                        break;

                                    default:
                                        if (is_string($values)) {
                                            $f .= $filterKey.' '.$condKey.' "'.$values.'"';
                                        } else {
                                            $f .= $filterKey.' '.$condKey.' '.$values;
                                        }
                                        break;
                                }
                            }
                        }

                        if ($f != '') {
                            $conditions[] = '('.$f.')';
                        }
                    }
                }
            }
        }
        return $conditions;
    }
}
