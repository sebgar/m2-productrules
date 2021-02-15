<?php
namespace Sga\ProductRules\Api\Data;

interface RuleInterface
{
    const RULE_ID = 'rule_id';
    const CODE = 'code';
    const IS_ACTIVE = 'is_active';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const MEMORY_LIMIT = 'memory_limit';
    const STORE_IDS = 'store_ids';
    const PRODUCT_LIMIT = 'product_limit';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const ACTIONS_SERIALIZED = 'actions_serialized';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const STARTED_AT = 'started_at';
    const FINISHED_AT = 'finished_at';

    /**
     * Get rule id
     *
     * @return int|null
     */
    public function getRuleId();

    /**
     * Set rule id
     *
     * @param int $id
     * @return RuleInterface
     */
    public function setRuleId($id);

    /**
     * Get code
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Set code
     *
     * @param string $value
     * @return RuleInterface
     */
    public function setCode($value);

    /**
     * Get is_active
     *
     * @return int|null
     */
    public function getIsActive();

    /**
     * Set is_active
     *
     * @param int $value
     * @return RuleInterface
     */
    public function setIsActive($value);

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Set title
     *
     * @param string $value
     * @return RuleInterface
     */
    public function setTitle($value);

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Set description
     *
     * @param string $value
     * @return RuleInterface
     */
    public function setDescription($value);

    /**
     * Get memory_limit
     *
     * @return string|null
     */
    public function getMemoryLimit();

    /**
     * Set memory_limit
     *
     * @param string $value
     * @return RuleInterface
     */
    public function setMemoryLimit($value);

    /**
     * Get store_ids
     *
     * @return string|null
     */
    public function getStoreIds();

    /**
     * Set store_ids
     *
     * @param string $value
     * @return RuleInterface
     */
    public function setStoreIds($value);

    /**
     * Get product_limit
     *
     * @return int|null
     */
    public function getProductLimit();

    /**
     * Set product_limit
     *
     * @param int $value
     * @return RuleInterface
     */
    public function setProductLimit($value);

    /**
     * Get conditions_serialized
     *
     * @return string|null
     */
    public function getConditionsSerialized();

    /**
     * Set conditions_serialized
     *
     * @param string $value
     * @return RuleInterface
     */
    public function setConditionsSerialized($value);

    /**
     * Get actions_serialized
     *
     * @return string|null
     */
    public function getActionsSerialized();

    /**
     * Set actions_serialized
     *
     * @param string $value
     * @return RuleInterface
     */
    public function setActionsSerialized($value);

    /**
     * Get created_at
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     *
     * @param string $value
     * @return RuleInterface
     */
    public function setCreatedAt($value);

    /**
     * Get updated_at
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     *
     * @param string $value
     * @return RuleInterface
     */
    public function setUpdatedAt($value);

    /**
     * Get started_at
     *
     * @return string|null
     */
    public function getStartedAt();

    /**
     * Set started_at
     *
     * @param string $value
     * @return RuleInterface
     */
    public function setStartedAt($value);

    /**
     * Get finished_at
     *
     * @return string|null
     */
    public function getFinishedAt();

    /**
     * Set finished_at
     *
     * @param string $value
     * @return RuleInterface
     */
    public function setFinishedAt($value);

}
