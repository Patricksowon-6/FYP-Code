<div id="formList">
  <div id="list">
    <div class="item add-card" id="addCard">
      <div style="display:flex; align-items:center; justify-content:center; height:100%;">
        <span style="font-size:50px; cursor:pointer;">➕</span>
      </div>
    </div>
  </div>
</div>

<!-- Edit Card Modal -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2 id="modalTitle">Edit Card</h2>
    <!-- IMPORTANT: enctype for file uploads -->
    <form id="editForm" enctype="multipart/form-data">
      <!-- hidden id for updates -->
      <input type="hidden" id="editId" name="id" value="">
      <label>Name:<br><input type="text" id="editName" name="name" required></label><br><br>
      <label>Purpose:<br><input type="text" id="editPurpose" name="purpose" required></label><br><br>
      <label>Card Type:<br>
        <select id="editType" name="card_type" required>
          <option value="">Select Type</option>
          <option value="Scene">Scene</option>
          <option value="Character">Character</option>
          <option value="Costume">Costume</option>
          <option value="Set">Set</option>
          <option value="Prop">Prop</option>
          <option value="Other">Other</option>
        </select>
      </label>
      <input type="text" id="editTypeOther" placeholder="Enter custom type" style="display:none; margin-top:5px;" name="card_type_other"><br><br>
      <label>Image:<br><input type="file" id="editImg" name="image" accept="image/*" required></label><br><br>
      <button type="submit" id="saveBtn">Save</button>
    </form>
  </div>
</div>