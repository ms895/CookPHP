<?php

/**
 * CookPHP framework
 *
 * @name CookPHP framework
 * @package CookPHP
 * @author CookPHP <admin@cookphp.org>
 * @version 0.0.1 Beta
 * @link http://www.cookphp.org
 * @copyright cookphp.org
 * @license <a href='http://www.cookphp.org'>CookPHP</a>
 */

namespace Libraries;

/**
 * 购物车类
 * @author 费尔 <admin@cookphp.org>
 */
class Cart {

    private $sessionId = '', $cookie = false, $itemLimit = 0, $quantityLimit = 99, $items = [], $attributes = [], $errors = [];

    /**
     * 初始购物车
     * @param boolean $cookie
     */
    public function __construct($cookie = false) {
        $this->sessionId = md5(($_SERVER['HTTP_HOST'] ?? 'CookPHP') . '_cart');
        $this->cookie = (bool) $cookie;
        $this->read();
    }

    /**
     * 返回错误
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * 返回最后一个错误
     * @return string
     */
    public function getLastError() {
        return end($this->errors);
    }

    /**
     * 获取购物车
     * @return array
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * 设置购物车中接受的每件商品的最大数量
     * @param integer $qty Quantity limit
     * @return boolean
     */
    public function setQuantityLimit($qty) {
        if (!$this->isInteger($qty)) {
            $this->errors[] = 'Cart::setQuantityLimit($qty): $qty must be integer.';
            return $this;
        }

        $this->quantityLimit = $qty;

        return $this;
    }

    /**
     * 设置购物车中接受的商品的最大值
     * @param integer $limit
     * @return boolean
     */
    public function setItemLimit($limit) {
        if (!$this->isInteger($limit)) {
            $this->errors[] = 'Cart::setItemLimit($limit): $limit must be integer.';
            return $this;
        }

        $this->itemLimit = $limit;

        return $this;
    }

    /**
     * 添加商品到购物车
     * @param integer $id
     * @param integer $qty
     *
     * @return boolean Result as true/false
     */
    public function add($id, $qty = 1) {
        if (!$this->isInteger($qty)) {
            $this->errors[] = 'Cart::add($qty): $qty must be integer.';
            return $this;
        }

        if ($this->itemLimit > 0 && count($this->items) >= $this->itemLimit) {
            $this->clear();
        }

        $this->items[$id] = (isset($this->items[$id])) ? ($this->items[$id] + $qty) : $qty;
        $this->items[$id] = ($this->items[$id] > $this->quantityLimit) ? $this->quantityLimit : $this->items[$id];

        $this->write();
        return $this;
    }

    /**
     * 设置商品属性
     * @param integer $id
     * @param string $key
     * @param string $value
     * @return boolean
     */
    public function setAttribute($id, $key = '', $value = '') {
        if (!isset($this->items[$id])) {
            $this->errors[] = 'Cart::setAttribute($id, $key, $value): Item #' . $id . ' does not exist.';
            return $this;
        }

        if (empty($key) || empty($value)) {
            $this->errors[] = 'Cart::setAttribute($id, $key, $value): Invalid value for $key or $value.';
            return $this;
        }

        $this->attributes[$id][$key] = $value;
        $this->write();

        return $this;
    }

    /**
     * 删除商品属性
     * @param integer $id
     * @param string $key
     */
    public function unsetAttribute($id, $key) {
        unset($this->attributes[$id][$key]);
    }

    /**
     * 获取商品属性
     * @param integer $id
     * @param string $key
     * @return string
     */
    public function getAttribute($id, $key) {
        if (!isset($this->attributes[$id][$key])) {
            $this->errors[] = 'Cart::getAttribute($id, $key): The attribute does not exist.';
            return $this;
        }

        return $this->attributes[$id][$key];
    }

    /**
     * 更新商品数量
     * @param integer $id
     * @param integer $qty
     * @return boolean
     */
    public function update($id, $qty) {
        if (!$this->isInteger($qty)) {
            $this->errors[] = 'Cart::update($id, $qty): $qty must be integer.';
            return $this;
        }

        if ($qty < 1) {
            return $this->remove($id);
        }

        $this->items[$id] = ($qty > $this->quantityLimit) ? $this->quantityLimit : $qty;
        $this->write();

        return $this;
    }

    /**
     * 删除商品
     * @param integer $id
     */
    public function remove($id) {
        unset($this->items[$id]);
        unset($this->attributes[$id]);
        $this->write();
    }

    /**
     * 清除购物车
     */
    public function clear() {
        $this->items = [];
        $this->attributes = [];
        $this->write();
    }

    /**
     * 擦除购物车会话和cookie
     */
    public function destroy() {
        unset($_SESSION[$this->sessionId]);

        if ($this->cookie) {
            Cookie::rm($this->sessionId, 86400);
        }

        $this->items = [];
        $this->attributes = [];
        return $this;
    }

    private function isInteger($int) {
        return Validate::isNumberId($int);
    }

    private function read() {
        $listItem = $this->cookie ? Cookie::get($this->sessionId) : ($_SESSION[$this->sessionId] ?? '');
        $listAttribute = $this->cookie ? Cookie::get($this->sessionId . '_attributes') : ($_SESSION[$this->sessionId . '_attributes'] ?? '');
        if (!empty($listItem)) {
            $items = explode(';', $listItem);
            foreach ($items as $item) {
                if (!$item || !strpos($item, ',')) {
                    continue;
                }
                list($id, $qty) = explode(',', $item);
                if ($this->isInteger($qty)) {
                    $this->items[$id] = (int) $qty;
                }
            }
        }
        if (!empty($listAttribute)) {
            $attributes = explode(';', $listAttribute);
            foreach ($attributes as $attribute) {
                if (!strpos($attribute, ',')) {
                    continue;
                }
                list($id, $key, $value) = explode(',', $attribute);
                $this->attributes[$id][$key] = $value;
            }
        }
    }

    private function write() {
        $_cart[$this->sessionId] = '';
        foreach ($this->items as $id => $qty) {
            if (!$id) {
                continue;
            }
            $_cart[$this->sessionId] .= $id . ',' . $qty . ';';
        }

        $_cart[$this->sessionId . '_attributes'] = '';
        foreach ($this->attributes as $id => $attributes) {
            if (!$id) {
                continue;
            }
            foreach ($attributes as $key => $value) {
                $_cart[$this->sessionId . '_attributes'] .= $id . ',' . $key . ',' . $value . ';';
            }
        }
        $_cart[$this->sessionId] = rtrim($_cart[$this->sessionId], ';');
        $_cart[$this->sessionId . '_attributes'] = rtrim($_cart[$this->sessionId . '_attributes'], ';');
        if ($this->cookie) {
            Cookie::set($this->sessionId, $_cart[$this->sessionId], 604800);
            Cookie::set($this->sessionId . '_attributes', $_cart[$this->sessionId . '_attributes'], 604800);
        } else {
            foreach ($_cart as $key => $value) {
                $_SESSION[$key] = $value;
            }
        }
    }

}
