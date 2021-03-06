<?php

class ModelKbmpmarketplaceCoupon extends Model {

    public function addCoupon($data, $seller_id) {
        // Seller free shiping is not possible to avoid multi seller cart shipping issue (Free Shipping will be applicable to all the sellers item so passed hardoded shipping as 0 instead '" . (int) $data['shipping'] . "
        $this->db->query("INSERT INTO " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', code = '" . $this->db->escape($data['code']) . "', discount = '" . (float) $data['discount'] . "', type = '" . $this->db->escape($data['type']) . "', total = '" . (float) $data['total'] . "', logged = '" . (int) $data['logged'] . "', shipping = '0', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int) $data['uses_total'] . "', uses_customer = '" . (int) $data['uses_customer'] . "', status = '" . (int) $data['status'] . "', date_added = NOW()");

        $coupon_id = $this->db->getLastId();

        $this->db->query("INSERT INTO `" . DB_PREFIX . "kb_mp_seller_coupon` SET seller_id = '" . (int) $seller_id . "', coupon_id = '" . (int) $coupon_id . "', store_id = '" . (int) $this->config->get('config_store_id') . "', date_added = NOW(), date_updated = NOW()");

        if (isset($data['coupon_product'])) {
            foreach ($data['coupon_product'] as $product_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_product SET coupon_id = '" . (int) $coupon_id . "', product_id = '" . (int) $product_id . "'");
            }
        } else {
            $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "kb_mp_seller_product WHERE seller_id = '" . (int) $seller_id . "'");
            if ($query->num_rows) {
                foreach ($query->rows as $key => $value) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_product SET coupon_id = '" . (int) $coupon_id . "', product_id = '" . (int) $value['product_id'] . "'");
                }
            }
        }

        if (isset($data['coupon_category'])) {
            foreach ($data['coupon_category'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_category SET coupon_id = '" . (int) $coupon_id . "', category_id = '" . (int) $category_id . "'");
            }
        }

        return $coupon_id;
    }

    public function editCoupon($coupon_id, $data) {
        // Seller free shiping is not possible to avoid multi seller cart shipping issue (Free Shipping will be applicable to all the sellers item so passed hardoded shipping as 0 instead '" . (int) $data['shipping'] . "
        $this->db->query("UPDATE " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', code = '" . $this->db->escape($data['code']) . "', discount = '" . (float) $data['discount'] . "', type = '" . $this->db->escape($data['type']) . "', total = '" . (float) $data['total'] . "', logged = '" . (int) $data['logged'] . "', shipping = '0', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int) $data['uses_total'] . "', uses_customer = '" . (int) $data['uses_customer'] . "', status = '" . (int) $data['status'] . "' WHERE coupon_id = '" . (int) $coupon_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int) $coupon_id . "'");

        if (isset($data['coupon_product'])) {
            foreach ($data['coupon_product'] as $product_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_product SET coupon_id = '" . (int) $coupon_id . "', product_id = '" . (int) $product_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category WHERE coupon_id = '" . (int) $coupon_id . "'");

        if (isset($data['coupon_category'])) {
            foreach ($data['coupon_category'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_category SET coupon_id = '" . (int) $coupon_id . "', category_id = '" . (int) $category_id . "'");
            }
        }
    }

    public function deleteCoupon($coupon_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int) $coupon_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int) $coupon_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category WHERE coupon_id = '" . (int) $coupon_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_history WHERE coupon_id = '" . (int) $coupon_id . "'");
    }

    public function getCoupon($coupon_id) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int) $coupon_id . "'");

        return $query->row;
    }

    public function getCouponByCode($code) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "coupon WHERE code = '" . $this->db->escape($code) . "'");

        return $query->row;
    }

    public function getCoupons($data = array(), $seller_id) {
        $sql = "SELECT c.coupon_id, name, code, discount, date_start, date_end, status FROM " . DB_PREFIX . "coupon as c"
                . " LEFT JOIN " . DB_PREFIX . "kb_mp_seller_coupon as sc ON c.coupon_id= sc.coupon_id "
                . "WHERE sc.seller_id = '" . (int) $seller_id . "' AND sc.seller_id IS NOT null";

        if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
            $sql .= " AND name LIKE '" . $this->db->escape(trim($data['filter_name'])) . "%'";
        }
        if (isset($data['filter_code']) && !is_null($data['filter_code'])) {
            $sql .= " AND code = '" . $this->db->escape(trim($data['filter_code'])) . "'";
        }
        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $sql .= " AND status = '" . (int) trim($data['filter_status']) . "'";
        }
        if (isset($data['filter_discount']) && !is_null($data['filter_discount'])) {
            $sql .= " AND discount = '" . (float) trim($data['filter_discount']) . "'";
        }
        if (isset($data['filter_date_start']) && !is_null($data['filter_date_start'])) {
            $sql .= " AND cast(date_start as date) = '" . trim($data['filter_date_start']) . "'";
        }
        if (isset($data['filter_date_end']) && !is_null($data['filter_date_end'])) {
            $sql .= " AND cast(date_end as date) = '" . trim($data['filter_date_end']) . "'";
        }
        $sort_data = array(
            'name',
            'code',
            'discount',
            'date_start',
            'date_end',
            'status'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getCouponProducts($coupon_id) {
        $coupon_product_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int) $coupon_id . "'");

        foreach ($query->rows as $result) {
            $coupon_product_data[] = $result['product_id'];
        }

        return $coupon_product_data;
    }

    public function getCouponCategories($coupon_id) {
        $coupon_category_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_category WHERE coupon_id = '" . (int) $coupon_id . "'");

        foreach ($query->rows as $result) {
            $coupon_category_data[] = $result['category_id'];
        }

        return $coupon_category_data;
    }

    public function getTotalCoupons() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "coupon");

        return $query->row['total'];
    }

    public function getCouponHistories($coupon_id, $start = 0, $limit = 10) {
        if ($start < 0) {
            $start = 0;
        }

        if ($limit < 1) {
            $limit = 10;
        }

        $query = $this->db->query("SELECT ch.order_id, CONCAT(c.firstname, ' ', c.lastname) AS customer, ch.amount, ch.date_added FROM " . DB_PREFIX . "coupon_history ch LEFT JOIN " . DB_PREFIX . "customer c ON (ch.customer_id = c.customer_id) WHERE ch.coupon_id = '" . (int) $coupon_id . "' ORDER BY ch.date_added ASC LIMIT " . (int) $start . "," . (int) $limit);

        return $query->rows;
    }

    public function getTotalCouponHistories($coupon_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "coupon_history WHERE coupon_id = '" . (int) $coupon_id . "'");
        return $query->row['total'];
    }

}
