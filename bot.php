try {
        return tx(function () use ($uid, $pid) {
            $p = one('SELECT * FROM products WHERE id=? AND active=1', [$pid]);
            if (!$p) throw new BizErr('gone');
            if (val('SELECT 1 FROM orders WHERE user_id=? AND product_id=? AND created_at>?', [$uid, $pid, time() - 3])) throw new BizErr('dup');
            
            $n = q('UPDATE users SET balance=balance-?, total_spent=total_spent+?, orders_count=orders_count+1 WHERE id=? AND balance>=?',
                [$p['price'], $p['price'], $uid, $p['price']])->rowCount();
            if (!$n) throw new BizErr('Insufficient balance.');
            
            $item = one('SELECT * FROM items WHERE product_id=? AND sold=0 LIMIT 1', [$pid]);
            if (!item) throw new BizErr('Out of stock.');
            
            $orderId = 'ORD' . time() . rand(100, 999);
            q('UPDATE items SET sold=1, order_id=? WHERE id=?', [$orderId, $item['id']]);
            
            q('INSERT INTO orders(id, user_id, product_id, product_name, amount, content, status, created_at) VALUES(?,?,?,?,?,?,?,?)',
                [$orderId, $uid, $pid, $p['name'], $p['price'], $item['content'], 'completed', time()]);
            
            $u = getUser($uid);
            if (!empty($u['referrer_id'])) {
                $refBonus = (in