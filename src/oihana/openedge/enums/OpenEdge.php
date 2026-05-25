<?php

namespace oihana\openedge\enums;

use oihana\enums\Char;
use oihana\models\enums\traits\ModelParamTrait;
use oihana\reflect\traits\ConstantsTrait;

/**
 * Enumeration of all OpenEdge "magic" constants used to interact with
 * OpenEdge tools and SQL features in your application.
 *
 * These constants can be used to build queries, define options, and
 * configure OpenEdge-specific behaviors in a standardized way.
 *
 * @package oihana\openedge\enums
 */
final class OpenEdge
{
    use ConstantsTrait ,
        ModelParamTrait ;

    /**
     * The 'alias' option.
     */
    public const string ALIAS = 'alias' ;

    /**
     * The 'alter' option.
     */
    public const string ALTER = 'alter' ;

    /**
     * The all (*) option.
     */
    public const string ALL = '*' ;

    /**
     * The 'array' option.
     */
    public const string ARRAY = 'array' ;

    /**
     * The 'bind' option.
     */
    public const string BIND = 'bind' ;

    /**
     * The 'cacheable' option.
     */
    public const string CACHEABLE = 'cacheable' ;

    /**
     * The 'cacheKey' option.
     */
    public const string CACHE_KEY = 'cacheKey' ;

    /**
     * The 'capitalize' option.
     */
    public const string CAPITALIZE = 'capitalize' ;

    /**
     * The 'case' option.
     */
    public const string CASE = 'case' ;

    /**
     * The 'cast' option.
     */
    public const string CAST = 'cast' ;

    /**
     * The 'conditions' option.
     */
    public const string CONDITIONS = 'conditions' ;

    /**
     * The 'container' option.
     */
    public const string CONTAINER = 'container' ;

    /**
     * The 'context' option.
     */
    public const string CONTEXT = 'context' ;

    /**
     * The 'controller' option.
     */
    public const string CONTROLLER = 'controller' ;

    /**
     * The 'column' option.
     */
    public const string COLUMN = 'column' ;

    /**
     * The 'columns' option.
     */
    public const string COLUMNS = 'columns' ;

    /**
     * The 'contact' option.
     */
    public const string CONCAT = 'concat' ;

    /**
     * The 'counter' option.
     */
    public const string COUNTER = 'counter' ;

    /**
     * The 'distinct' option.
     */
    public const string DISTINCT = 'distinct' ;

    /**
     * The 'else' option.
     */
    public const string ELSE = 'else' ;

    /**
     * The 'expression' option.
     */
    public const string EXPRESSION = 'expression' ;

    /**
     * The 'facet' option.
     */
    public const string FACET = 'facet' ;

    /**
     * The 'facets' option.
     */
    public const string FACETS = 'facets' ;

    /**
     * The 'filter' option.
     */
    public const string FILTER = 'filter'   ;

    /**
     * The 'from' option.
     */
    public const string FROM = 'from' ;

    /**
     * The 'groupBy' option.
     */
    public const string GROUP_BY = 'groupBy' ;

    /**
     * The 'harvest' option.
     */
    public const string HARVEST = 'harvest' ;

    /**
     * The 'having' option.
     */
    public const string HAVING = 'having' ;

    /**
     * The 'join' option.
     */
    public const string JOIN = 'join' ;

    /**
     * The 'joins' option.
     */
    public const string JOINS = 'joins' ;

    /**
     * The 'limit' option.
     */
    public const string LIMIT = 'limit' ;

    /**
     * The 'literal' option.
     */
    public const string LITERAL = 'literal' ;

    /**
     * The 'lockingHint' option.
     */
    public const string LOCKING_HINT = 'lockingHint' ;

    /**
     * The 'milliseconds' option.
     */
    public const string MILLISECONDS = 'milliseconds' ;

    /**
     * The 'name' option.
     */
    public const string NAME = 'name' ;

    /**
     * The 'nolock' option.
     */
    public const string NOLOCK = 'nolock' ;

    /**
     * The 'noReorder' option.
     */
    public const string NO_REORDER = 'noReorder' ;

    /**
     * The 'nullable' option.
     */
    public const string NULLABLE = 'nullable' ;

    /**
     * The nullable column suffix -> (+)
     * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-reference/page/Outer-join-predicate.html
     * @example
     * SELECT e.nom, e.id_department, d.name_department
     * FROM employees e, departments d
     * WHERE e.id_department = d.id_department(+);
     */
    public const string NULLABLE_COLUMN = Char::LEFT_PARENTHESIS . Char::PLUS . Char::RIGHT_PARENTHESIS  ;

    /**
     * The 'offset' option.
     */
    public const string OFFSET = 'offset' ;

    /**
     * The 'on' option.
     */
    public const string ON = 'on' ;

    /**
     * The 'operator' option.
     */
    public const string OPERATOR = 'operator' ;

    /**
     * The 'options' option.
     */
    public const string OPTIONS = 'options' ;

    /**
     * The 'orderBy' option.
     */
    public const string ORDER_BY = 'orderBy' ;

    /**
     * The 'params' option.
     */
    public const string PARAMS = 'params' ;

    /**
     * The 'path' option.
     */
    public const string PATH = 'path' ;

    /**
     * The 'pattern' option.
     */
    public const string PATTERN = 'pattern' ;

    /**
     * The 'pdo' option.
     */
    public const string PDO = 'pdo' ;

    /**
     * The 'predicate' option.
     */
    public const string PREDICATE = 'predicate' ;

    /**
     * The 'query' option.
     */
    public const string QUERY = 'query' ;

    /**
     * The 'route' option.
     */
    public const string ROUTE = 'route' ;

    /**
     * The 'schema' option.
     */
    public const string SCHEMA = 'schema' ;

    /**
     * The 'separator' option.
     */
    public const string SEPARATOR = 'separator' ;

    /**
     * The 'sort' option.
     */
    public const string SORT = 'sort' ;

    /**
     * The 'sortable' option.
     */
    public const string SORTABLE = 'sortable' ;

    /**
     * The 'table' option.
     */
    public const string TABLE = 'table' ;

    /**
     * The 'then' option.
     */
    public const string THEN = 'then' ;

    /**
     * The 'timezone' option.
     */
    public const string TIMEZONE = 'timezone' ;

    /**
     * The 'top' option.
     */
    public const string TOP = 'top' ;

    /**
     * The 'type' option.
     */
    public const string TYPE = 'type' ;

    /**
     * The 'url' option.
     */
    public const string URL = 'url' ;

    /**
     * The 'useParentheses' option.
     */
    public const string USE_PARENTHESES = 'useParentheses' ;

    /**
     * The 'when' option.
     */
    public const string WHEN = 'when' ;

    /**
     * The 'with' option.
     */
    public const string WITH = 'with' ;

    /**
     * The 'where' option.
     */
    public const string WHERE = 'where' ;
}