<?php

$open_close_account = [
                            'open.close.account',
                            'customer.setup',
                            'close.account',
                            'installed.meters',
                        ];

$crm_function = [
                            'crm.functions',
                            'customer.search',
                            'crm.barcode.reports',
                            'message.all.customers',
                        ];

$system_reports = [
                            'system.reports',
                            'supply.report.units',
                                'boiler.report',
                            'topup.reports',
                            'customer.topup.history',
                            'tariff.history',
                            'credit.amount.in.system',
                            'barcode.reports',
                            'sms.messages.sent',
                            'list.all.customers',
                            'deleted.customer.report',
                            'inactive.landlords.report',
                            'deposit.report',
                            'credit.issue.report',
                                'iou.usage.display',
                                'iou.extra.usage.display',
                                'admin.issued.credit',
                            'weather.report',
                                'weather.vs.topups',
                                'weather.vs.heat.usage',
                            'bill.reports',
                            'payout.reports',
                            'not.read.meters.reports',
                        ];

$settings = [
                            'settings',
                            'admin.settings',
                                'sms.settings',
                                'faq.settings',
                                'tariff.settings',
                                'credit.setting',
                                'access.control',
                                'unassigned.users',
                                /*'multiple.account.close',
                            'user.settings',
                                'change.username',
                                'change.password'*/
                                'groups.permissions',
                                'schemes.list',
                                'scheme.setup',
                            'boss',
                                'boss.hierarchy',
                        ];

return [

  'all' => array_merge($open_close_account, $crm_function, $system_reports, $settings),

  'group1' => $open_close_account,

  'group2' => array_merge($open_close_account, $crm_function),

  'group3' => array_merge($open_close_account, $crm_function, $system_reports),

  'group4' => array_merge($open_close_account, $crm_function, $system_reports, $settings),

];
