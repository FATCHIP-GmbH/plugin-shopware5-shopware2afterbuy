<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{link file="backend/_resources/css/bootstrap.min.css"}">
    <style type="text/css">
        body[role=document] {
            padding: 20px 10px;
            background-color: white;
        }
        .table {
            table-layout: fixed;
        }
        .table>thead {
            background-color: whitesmoke;
        }
        .table>thead>tr>th {
            border-bottom: none;
        }
        .table>tbody>tr>td {
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }
        .table-hover>tbody>tr:hover {
            cursor: pointer;
        }
        .label {
            display: inline-block;
            min-width: 35px;
        }
        .label.label-default {
            background-color: #888;
        }
        .breadcrumb {
            border: 1px solid #ddd;
        }
        .nav-tabs>li>a {
            border-color: #eee #eee #ddd;
        }
        .nav-tabs>li.active>a,
        .nav-tabs>li.active>a:hover,
        .nav-tabs>li.active>a:focus {
            background-color: #f5f5f5;
            font-weight: 500;
        }
        .tab-panel,
        .tab-panel>.panel-heading {
            border-top-left-radius: 0;
            border-top-right-radius: 0;
            border-top-width: 0;
        }
        .tab-panel>.panel-body {
            margin: 4px;
        }
        a.list-group-item.expanded:nth-child(2) {
            border-top-right-radius: 4px;
            border-top-left-radius: 4px;
        }
        a.list-group-item.collapsed:nth-last-child(2) {
            border-bottom-right-radius: 4px;
            border-bottom-left-radius: 4px;
        }
        a.list-group-item.expanded {
            display: none;
        }
        a.list-group-item.collapsed:target {
            display: none;
        }
        a.list-group-item.collapsed:target + a.list-group-item.expanded {
            display: block;
            background-color: whitesmoke;
        }
        div.item-details {
            padding: 12px 20px 4px 20px;
            margin: 10px 4px 4px 0;
            background-color: white;
            border: 1px dashed lightgrey;
            border-radius: 4px;
        }
        .badge {
            font-size: small;
            background-color: #f5f5f5;
            border: 1px solid #C0C0C0;
            color: #544949;
        }
        .badge > span:first-child {
            letter-spacing: 1px;
            font-weight: 300;
        }
        .list-group + span.pull-right {
            padding-right: 2px;
        }
        .form-control[readonly] {
            background-color: white;
        }
        .form-control[disabled] {
            color: #333;
        }
        #status-box {
            padding: 2px 6px;
            border: 1px solid lightgrey;
            border-radius: 4px;
            font-weight: bold;
        }
        #status-wrapper {
            padding-top: 6px;
        }
        #afterbuy-logo {
            top: 8px;
            right: 28px;
            height: 42px;
            width: 250px;
            display: block;
            position: absolute;
            background: url({link file='backend/_resources/images/logo-large.png'});
            background-repeat: no-repeat;
            background-position: center;
            background-size: 100%;
        }
        @media (max-width:480px) {
            body[role=document] {
                padding: 60px 10px;
            }
        }
        @media (min-width:768px) {
            #pickupParcelCount div.form-group.form-group-sm {
                margin-right: 2px;
            }
            input.form-control.field-tiny,
            select.form-control.field-tiny {
                width: 50px;
            }
            input.form-control.field-narrow,
            select.form-control.field-narrow {
                width: 75px;
            }
            input.form-control.field-half,
            select.form-control.field-half {
                width: 128px;
            }
            input.form-control.field-normal,
            select.form-control.field-normal {
                width: 180px;
            }
            input.form-control.field-wide,
            select.form-control.field-wide {
                width: 259px;
            }
        }
    </style>
</head>
<body role="document">

<div id="afterbuy-logo">&nbsp;</div>
<div class="container-fluid">
    <ul class="nav nav-tabs">
        <li role="menuitem" class="active">
            <a href="{url action=pluginConfig}">Konfiguration</a>
        </li>
        <!--
        <li role="menuitem">
            <a href="#">Logs</a>
        </li>
        -->
    </ul>
    <div class="panel panel-default tab-panel">
        <div class="panel-heading">
            {block name='backend_admin_afterbuy_title'}{/block}
        </div>
        <div class="panel-body">
            {block name='backend_admin_afterbuy_content'}{/block}
        </div>
        <div class="panel-footer">
            {block name='backend_admin_afterbuy_footer'}{/block}
        </div>
    </div>
</div>

<script type="text/javascript" src="{link file="backend/base/frame/postmessage-api.js"}"></script>
<script type="text/javascript" src="{link file="backend/_resources/js/jquery-3.1.1.min.js"}"></script>
<script type="text/javascript" src="{link file="backend/_resources/js/bootstrap.min.js"}"></script>
<script type="text/javascript">
    var onLoadPostMessages = [
        {if !empty($maxHeight)}
            function () {
                postMessageApi.window.getHeight(function (height) {
                    if (height > {$maxHeight}) {
                        postMessageApi.window.setHeight({$maxHeight});
                    }
                });
            }
        {/if}
    ];
</script>

{block name='backend_admin_afterbuy_script'}{/block}

<script type="text/javascript">
    $(window).on('load', function () {
        setTimeout(function () {
            onLoadPostMessages.forEach(function (callable) {
                callable();
            })
        }, 16);
    });
</script>

</body>
</html>
