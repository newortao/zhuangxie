<?php
include 'includes/auth.php';
include 'db.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// 设置默认日期范围
$start_date = date('Y-m-d', strtotime('-7 days'));
$end_date = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
}

// 查询记录 - 添加 product_name 字段
$sql = "SELECT r.id, r.record_date, w.name AS worker_name, 
               c.name AS category_name, r.quantity, 
               r.total_price, u.username AS recorded_by,
               r.product_name  -- 新增商品名称字段
        FROM records r
        JOIN workers w ON r.worker_id = w.id
        JOIN categories c ON r.category_id = c.id
        JOIN users u ON r.recorded_by = u.id
        WHERE r.record_date BETWEEN ? AND ?
        ORDER BY r.record_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$start_date, $end_date]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "装卸记录查询";
$breadcrumb = "记录查询";
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">查询条件</h3>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="form-row">
                <div class="form-group col-md-5">
                    <label>开始日期</label>
                    <input type="date" name="start_date" value="<?= $start_date ?>" class="form-control" required>
                </div>
                <div class="form-group col-md-5">
                    <label>结束日期</label>
                    <input type="date" name="end_date" value="<?= $end_date ?>" class="form-control" required>
                </div>
                <div class="form-group col-md-2" style="align-self: flex-end;">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> 查询
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">查询结果</h3>
        <div>
            <a href="export_excel.php?start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" 
               class="btn btn-excel">
               <i class="fas fa-file-excel"></i> 导出Excel
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>日期</th>
                        <th>商品名称</th>
                        <th>品类</th>
                        <th>公司名称</th> <!-- 新增商品名称列 -->
                        <th>数量</th>
                        <th>金额</th>
                        <th>登记人</th>
                        <?php if (isAdmin()): ?>
                            <th>操作</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?= $record['record_date'] ?></td>
                        <td><?= htmlspecialchars($record['product_name']) ?></td> <!-- 显示商品名称 -->
                        <td><?= htmlspecialchars($record['category_name']) ?></td> <!-- 显示品类 -->
                        <td><?= htmlspecialchars($record['worker_name']) ?></td> <!-- 显示公司名称 -->
                        <td><?= $record['quantity'] ?></td>
                        <td>¥<?= number_format($record['total_price'], 2) ?></td>
                        <td><?= $record['recorded_by'] ?></td>
                        <?php if (isAdmin()): ?>
                            <td class="action-buttons">
                                <a href="edit_record.php?id=<?= $record['id'] ?>" 
                                   class="btn-action btn-edit" title="编辑">
                                   <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_record.php?id=<?= $record['id'] ?>" 
                                   class="btn-action btn-delete" 
                                   title="删除"
                                   onclick="return confirm('确定删除这条记录吗？')">
                                   <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>