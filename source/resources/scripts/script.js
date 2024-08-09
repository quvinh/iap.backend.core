function doGet(e) {
  var functionName = e.parameter.function;
  var scriptId = e.parameter.script_id;

  if (!scriptId) return response(442, "Script Id not found");
  if (!checkSpreadsheetExists(scriptId)) return response(404, "Script Id invalid");

  if (functionName == 'functionTest') {
    return functionOne();
  } else if (functionName == 'onLoadSold') {
    return onLoadSold(scriptId);
  } else {
    return response(442, "Function not found");
  }
}

function functionTest() {
  return ContentService.createTextOutput("Function Test Executed");
}

function response(status, message) {
  return ContentService
    .createTextOutput(JSON.stringify({ status: status || 200, message }))
    .setMimeType(ContentService.MimeType.JSON);
}

function checkSpreadsheetExists(spreadsheetId) {
  try {
    SpreadsheetApp.openById(spreadsheetId);
    return true;
  } catch (e) {
    return false;
  }
}

/**
 * On load sold
 */
function onLoadSold(spreadsheetId) {
  // Get the active spreadsheet and sheets
  // var ss = SpreadsheetApp.getActiveSpreadsheet();
  // var spreadsheetId = "1hTcVVGQrnJO2kyqe6MEbL0k2TcYIB22v-zsui10mgMg";
  var ss = SpreadsheetApp.openById(spreadsheetId);
  var sheet = ss.getSheetByName("Banra3-2024");

  // Column Q
  setStyleColumn(sheet, 17, 120, "Mã tồn");
  // Column R
  setStyleColumn(sheet, 18, 120, "Mã (IAP)");
  setStyleColumn(sheet, 19, 120, "Running");

  // Get the first and last row index of the sheet
  var firstRowIndex = sheet.getRange(2, 1).getRow();
  var lastRowIndex = sheet.getLastRow();
  var dataOpeningBalance = loadOpeningBalance(spreadsheetId);
  var dataFromDB = findCodesByApi(54, 2024);
  var threshold = 0.5; // 50% similarity threshold

  for (var i = firstRowIndex; i <= lastRowIndex; i++) {
    var balanceCell = sheet.getRange(i, 17);
    var apiCell = sheet.getRange(i, 18);
    var logCell = sheet.getRange(i, 19);

    if (!balanceCell.getValue() && !apiCell.getValue()) {
      var productNameCell = sheet.getRange(i, 9).getValue();
      try {
        if (productNameCell) {
          Logger.log("Running at row " + i);
          balanceCell.setBackground("white");
          apiCell.setBackground("white");
          logCell.setValue(Utilities.formatDate(new Date(), 'Asia/Ho_Chi_Minh', 'HH:mm:ss'));
          logToSheet(spreadsheetId, "Row " + i);

          var resultBalance = findSimilarProducts(dataOpeningBalance, productNameCell, threshold);
          if (resultBalance) {
            balanceCell.setValue(resultBalance);
          } else {
            balanceCell.setBackground("orange");

            var resultApi = findSimilarProducts(dataFromDB, productNameCell, threshold);
            if (resultApi) {
              apiCell.setValue(resultBalance);
            } else {
              apiCell.setBackground("orange");
            }
          }
        } else {
          balanceCell.setBackground("red");
          apiCell.setBackground("red");
          Logger.log("Product not found at row " + i);
        }
      } catch (e) {
        Logger.log("Error at row " + i + ":" + e.message);
        balanceCell.setBackground("red");
        apiCell.setBackground("red");
      }
    }
  }
}

/**
 * Load file Ton dau ky
 */
function loadOpeningBalance(spreadsheetId) {
  // Get the active spreadsheet and sheets
  // var ss = SpreadsheetApp.getActiveSpreadsheet();
  var ss = SpreadsheetApp.openById(spreadsheetId);
  var sheet = ss.getSheetByName("Ton đầu kỳ 2024");

  // Find the corresponding code in Sheet
  var rows = sheet.getDataRange().getValues();
  var result = rows.filter(function (row) {
    return !isNaN(row[4]) && typeof row[4] === 'number';
  }).map(function (row) {
    return {
      product_code: row[1] || null,
      product: row[2] || null,
      unit: row[3] || null,
      quantity: row[4] || null,
      money: row[5] || null,
    }
  });
  return result;
}

/**
 * Set style column
 */
function setStyleColumn(sheet, column, width, text, row) {
  row = row || 1;
  sheet.getRange(row, column).setValue(text);
  sheet.setColumnWidth(column, width);
  sheet.getRange(row, column).setFontWeight('bold');
}

function findSimilarProducts(products, searchString, threshold) {

  for (var i = 0; i < products.length; i++) {
    var product = products[i];
    var productName = product.product;

    // Ensure productName and searchString are not null or undefined
    if (productName && searchString) {
      var productNameLower = productName.toLowerCase();
      var searchLower = searchString.toLowerCase();

      var similarity = similarityCalculate(productNameLower, searchLower);

      if (similarity >= threshold) {
        return product.product_code;
      }
    }
  }
  return null;
}

function similarityCalculate(s1, s2) {
  var longer = s1
  var shorter = s2
  if (s1.length < s2.length) {
    longer = s2
    shorter = s1
  }
  const longerLength = longer.length
  if (longerLength === 0) {
    return 1.0
  }
  return (longerLength - editDistance(longer, shorter)) / parseFloat(longerLength)
}

function editDistance(s1, s2) {
  s1 = s1.toLowerCase()
  s2 = s2.toLowerCase()

  const costs = []
  for (var i = 0; i <= s1.length; i++) {
    var lastValue = i
    for (var j = 0; j <= s2.length; j++) {
      if (i == 0) costs[j] = j
      else {
        if (j > 0) {
          var newValue = costs[j - 1]
          if (s1.charAt(i - 1) != s2.charAt(j - 1))
            newValue = Math.min(Math.min(newValue, lastValue), costs[j]) + 1
          costs[j - 1] = lastValue
          lastValue = newValue
        }
      }
    }
    if (i > 0) costs[s2.length] = lastValue
  }
  return costs[s2.length]
}

/**
 * LogSheet
 */
function logToSheet(spreadsheetId, message) {
  // var ss = SpreadsheetApp.getActiveSpreadsheet();
  var ss = SpreadsheetApp.openById(spreadsheetId);
  var sheet = ss.getSheetByName("LogSheet");
  if (!sheet) {
    sheet = ss.insertSheet("LogSheet");
    ss.moveActiveSheet(ss.getSheets().length);
  }
  sheet.appendRow([new Date(), message]);
}

/**
 * Fetch api
 */
function findCodesByApi(companyId, year) {
  var options = {
    method: "GET",
    headers: {
      "Accept": "application/json",
      "Authorization": "APPSCRIPT API",
    }
  };
  var params = "?company_id=" + companyId + "&year=" + year;
  var api = UrlFetchApp.fetch("https://api.iap.vn/api/item-codes" + params, options);
  var response = JSON.parse(api.getContentText());
  if (response.result) {
    var result = response.data.message;
    return result;
  }
  Logger.log("Data from api not found");
  return [];
}
