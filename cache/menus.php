<?php return array (
  '交易管理' => 
  array (
    '交易管理' => 
    array (
      '历史交易' => '?a=order&m=history,',
      '取货记录' => '?a=order&m=quhuo,2',
      '删除收支记录' => '?a=order&m=paydetail_delete,1',
      '删除销售记录' => '?a=order&m=saledetail_delete,1',
      '收支明细' => '?a=order&m=pay_detail,',
    ),
    '网上交易' => 
    array (
      '订单管理' => '?a=order&m=online_order,',
      '测试员支付统计' => '?a=order&m=test_pay_count,2',
    ),
    '数据报表' => 
    array (
      '收支统计' => '?a=report&m=pay,',
      '收支统计_单机' => '?a=report&m=pay_machine,',
      '交易统计' => '?a=report&m=sale,',
      '交易统计_单机' => '?a=report&m=sale_machine,',
      '销售商品统计' => '?a=report&m=goods,2',
      '第三方支付统计' => '?a=report&m=pay_online,2',
      '采购报表' => '?a=report&m=purchases,2',
    ),
  ),
  '会员系统' => 
  array (
    '会员管理' => 
    array (
      '会员管理' => '?a=huiyuan&m=member,2',
      '会员等级' => '?a=huiyuan&m=degree,2',
    ),
    '代理商管理' => 
    array (
      '代理商管理' => '?a=huiyuan&m=agent,2',
    ),
  ),
  '库存管理' => 
  array (
    '商品库存' => 
    array (
      '商品管理' => '?a=goods&m=goods_list,',
      '商品分类' => '?a=goods&m=goods_type,',
      '创建分类' => '?a=goods&m=add_type,1',
      '修改分类' => '?a=goods&m=edit_type,1',
      '删除分类' => '?a=goods&m=delete_type,1',
      '库存修改' => '?a=goods&m=goods_update,1',
      '供应商管理' => '?a=purchases&m=supplier,2',
      '采购管理' => '?a=purchases&m=purchases,2',
    ),
  ),
  '机器管理' => 
  array (
    '货道管理' => 
    array (
      '货道管理' => '?a=road&m=machine_goods,',
      '货道复制' => '?a=road&m=copy_road,1',
      '批量添加货道' => '?a=road&m=add_road_bat,2',
      '批量设置价格' => '?a=road&m=edit_price_bat,2',
      '批量设置容量' => '?a=road&m=edit_num_bat,2',
      '一键上货' => '?a=road&m=add_goods_bat,2',
      '货道分布图' => '?a=road&m=roads_map,2',
      '自动配置货道' => '?a=machine&m=road_test,2',
    ),
    '机器管理' => 
    array (
      '机器分布图' => '?a=map&m=machine_map,',
      '查看所有机器' => '?a=machine&m=all,1',
      '我的机器' => '?a=machine&m=index,',
      '分组管理' => '?a=machine&m=group,2',
      '创建机器' => '?a=machine&m=add_machine,',
      '修改机器' => '?a=machine&m=edit_machine,1',
      '删除机器' => '?a=machine&m=delete_machine,1',
      '上报机器状态' => '?a=machine&m=set_status,1',
      '机器温度' => '?a=machine&m=temperature,1',
      '机器状态' => '?a=machine&m=status,',
      '指定管理员' => '?a=machine&m=set_user,1',
      '机器测试' => '?a=machine&m=machine_test,',
      '机器设置' => '?a=machine&m=machine_set,',
      '清空历史记录' => '?a=machine&m=clear,',
      '单片机升级' => '?a=machine&m=set_update,1',
      '重置机器密码' => '?a=machine&m=reset_machine_pwd,1',
      '设备故障' => '?a=machine&m=warn,',
      '二维码下载' => '?a=report&m=qrcode,2',
    ),
  ),
  '校园卡管理' => 
  array (
    '校园卡' => 
    array (
      '卡片管理' => '?a=schoolcard&m=index,2',
      '发卡' => '?a=schoolcard&m=cardinfo,2',
      '删除卡' => '?a=schoolcard&m=delete,1',
      '充值扣款' => '?a=schoolcard&m=money,2',
      '资金流水' => '?a=schoolcard&m=logs,2',
      '充值记录' => '?a=schoolcard&m=order,2',
    ),
    '家长管理' => 
    array (
      '家长管理' => '?a=schoolcard&m=parents,',
    ),
  ),
  '游戏管理' => 
  array (
    '幸运转盘' => 
    array (
      '游戏设置' => '?a=game&m=xyzp_config,2',
      '中奖记录' => '?a=game&m=xyzp_list,',
    ),
    '抽奖游戏' => 
    array (
      '游戏设置' => '?a=game&m=zpcj_config,',
      '中奖记录' => '?a=game&m=zpcj_list,',
      '支付记录' => '?a=game&m=zpcj_list,',
    ),
  ),
  '礼品机管理' => 
  array (
    '微信公众平台' => 
    array (
      '公众号管理' => '?a=weixin_mp&m=index,2',
      '礼品绑定' => '?a=weixin_mp&m=vm_mp,2',
      '自动回复' => '?a=weixin_mp&m=auto,1',
      '图文信息' => '?a=weixin_mp&m=article,1',
      '自定义菜单' => '?a=weixin_mp&m=menu,1',
    ),
    '普通礼品派送' => 
    array (
      '公众号管理' => '?a=weichat&m=index,2',
      '添加公众号' => '?a=weichat&m=index&act=add,2',
      '批量绑定公众号' => '?a=weichat&m=ban_bat,2',
      '广告设置' => '?a=weichat&m=ad,2',
    ),
    '礼品记录' => 
    array (
      '礼品发放记录' => '?a=weichat&m=prizelog,2',
      '删除领奖记录' => '?a=weichat&m=del_prizelog,1',
    ),
  ),
  '系统管理' => 
  array (
    '短信管理' => 
    array (
      '短信发送记录' => '?a=sms&m=index,2',
      '修改短信条数' => '?a=sms&m=set_sms_num,1',
      '发送短信' => '?a=sms&m=send,2',
    ),
    '留言管理' => 
    array (
      '留言记录' => '?a=messagebox&m=index,2',
    ),
    '管理员管理' => 
    array (
      '管理员列表' => '?a=users&m=index,2',
      '管理员管理' => '?a=users&m=sons,2',
      '添加管理员' => '?a=users&m=add,2',
      '修改管理员资料' => '?a=users&m=edit,1',
      '删除管理员' => '?a=users&m=delete,1',
    ),
    '账户管理' => 
    array (
      '修改密码' => '?a=users&m=password,',
      '授权修改收款账号' => '?a=users&m=auth_set,1',
      '账户设置' => '?a=users&m=userinfo,',
      '资金流水' => '?a=users&m=money_logs,',
      '提现记录' => '?a=users&m=tixian_logs,',
      '我要提现' => '?a=users&m=tixian_apply,',
      '运营商资金结算' => '?a=users&m=set_usermoeny_2,1',
      '微信自动转账记录' => '?a=weixin&m=index_user,',
    ),
    '系统管理' => 
    array (
      '重置密码' => '?a=users&m=resetpwd,1',
      '系统设置' => '?a=system&m=index,2',
      '第三方支付设置' => '?a=system&m=pay,2',
    ),
    '日志管理' => 
    array (
      '登录日志' => '?a=logs&m=index,',
      '操作日志' => '?a=logs&m=operate,',
      '机器日志' => '?a=logs&m=machine,2',
      '日志搜索' => '?a=logs&m=search,1',
    ),
    '权限分配' => 
    array (
      '权限组管理' => '?a=rule&m=index,2',
      '创建分组' => '?a=rule&m=add_group,2',
      '修改权限组' => '?a=rule&m=edit,1',
      'SQL操作' => '?a=rule&m=sql,2',
      '删除分组' => '?a=rule&m=delete,1',
    ),
  ),
  '网站管理' => 
  array (
    '权限分配' => 
    array (
      '更新菜单' => '?a=index&m=init_menu,1',
      '授权登录' => '?a=index&m=authorization,1',
    ),
  ),
  '视频广告' => 
  array (
    '视频广告' => 
    array (
      '广告管理' => '?a=videoads&m=ads,2',
      '素材管理' => '?a=videoads&m=material,2',
      '广告计划' => '?a=videoads&m=adsplan,2',
    ),
  ),
  '财务管理' => 
  array (
    '提现管理' => 
    array (
      '提现记录' => '?a=users&m=tixian_cw_logs,2',
      '处理提现' => '?a=users&m=tixian_deal,1',
    ),
    '微信转账' => 
    array (
      '转账记录' => '?a=weixin&m=index,2',
      '转账给个人' => '?a=weixin&m=transfer,1',
    ),
  ),
);