<ul>
  <?php foreach ($categories as $category): ?>
    <li><?php echo htmlspecialchars($category->name) ?></li>
  <?php endforeach ?>
</ul>