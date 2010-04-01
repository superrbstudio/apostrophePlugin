<td class="batch-actions first">
  [?php if ((!method_exists($<?php echo $this->getSingularName() ?>, 'userHasPrivilege')) || $<?php echo $this->getSingularName() ?>->userHasPrivilege('edit')): ?]
  <input type="checkbox" name="ids[]" value="[?php echo $<?php echo $this->getSingularName() ?>->getPrimaryKey() ?]" class="a-admin-batch-checkbox a-checkbox" />
  [?php endif; ?]
</td>
