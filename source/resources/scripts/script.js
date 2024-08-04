function createOnEditTrigger() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  ScriptApp.newTrigger('onEdit')
    .forSpreadsheet(ss)
    .onEdit()
    .create();
}

function onEdit(e) {
  var sheetName = "Sheet1"; // Tên sheet chỉ định
  var sheet = e.source.getSheetByName(sheetName);
  if (sheet) {
    var range = e.range;
    var value = range.getValue();

    Logger.log('Cell edited in ' + sheetName + ': ' + range.getA1Notation() + ' Value: ' + value);

    // Thêm code của bạn ở đây để xử lý khi chỉnh sửa
    processInBatches(sheet);
  }
}

function processInBatches(sheet) {
  var data = sheet.getDataRange().getValues();
  var batchSize = 100; // Kích thước batch

  for (var i = 0; i < data.length; i += batchSize) {
    var batch = data.slice(i, i + batchSize);
    processBatch(batch);
  }
}

function processBatch(batch) {
  // Xử lý dữ liệu trong batch
  for (var i = 0; i < batch.length; i++) {
    Logger.log('Processing row: ' + batch[i]);
    // Thêm code xử lý dữ liệu ở đây
  }
}
