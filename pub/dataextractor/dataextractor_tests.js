
// tests
var source = '<f1>a</f1>';

// 1) test empty
var res = generateCSV();
if (res.csv != '' || res.markers.length != 0) {
  throw new Error("Test 1 failed!");
}

// 2) test with source only
res = generateCSV('aa');
if (res.csv != '' || res.markers.length != 0) {
  throw new Error("Test 2 failed!");
}

// 3) test with source and field names only
source = 'aa';
var f1 = ['f1'];
var newf1 = [{ fieldName: f1 }];
res = generateCSV(source, newf1);
if (res.csv != 'f1;\n' || res.markers.length != 0) {
  throw new Error("Test 3 failed!");
}

// 4) simple example
source = '<1>a</1><1>b</1>';
var f1Start = ['<1>'];
var f1StartCol = ['rgb(255, 0, 0)'];
var f1End = ['</1>'];
var f1EndCol = ['rgb(0, 0, 255)'];
newf1 = [{
  fieldName: f1[0], start: f1Start[0], startCol: f1StartCol[0], end: f1End[0], endCol: f1EndCol[0]
}];
res = generateCSV(source, newf1);
if (res.csv != 'f1;\na;\nb;\n' || JSON.stringify(res.markers) != JSON.stringify([
  {"start": {"line": 0, "ch": 0},
   "end": {"line": 0, "ch": 3},
   "class": {"className": "cm-f1_start"}
  },
  {"start": {"line": 0, "ch": 4},
   "end": {"line": 0, "ch": 8},
   "class": {"className": "cm-f1_end"}
  },
  {"start": {"line": 0, "ch": 8},
   "end": {"line": 0, "ch": 11},
   "class": {"className": "cm-f1_start"}
  },
  {"start": {"line": 0, "ch": 12},
   "end": {"line": 0, "ch": 16},
   "class": {"className": "cm-f1_end"}
  }
]))
{
  throw new Error("Test 4 failed!");
}

// 5) with 2 columns
source = '<1>a</1><2>b</2><1>c</1>';
var f2 = ['f2'];
var f2Start = ['<2>'];
var f2StartCol = ['rgb(255, 0, 0)'];
var f2End = ['</2>'];
var f2EndCol = ['rgb(0, 0, 255)'];
newf1 = [
  ...newf1,
  { fieldName: f2[0], start: f2Start[0], startCol: f2StartCol[0], end: f2End[0], endCol: f2EndCol[0] }
];
res = generateCSV(source, newf1);
if (res.csv != 'f1;f2;\na;b;\nc;;\n' || JSON.stringify(res.markers) != JSON.stringify([
  {"start": {"line": 0, "ch": 0},
    "end": {"line": 0, "ch": 3},
    "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 4},
    "end": {"line": 0, "ch": 8},
    "class": {"className": "cm-f1_end"}},
  {"start": {"line": 0, "ch": 8},
    "end": {"line": 0, "ch": 11},
    "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 12},
    "end": {"line": 0, "ch": 16},
    "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 16},
    "end": {"line": 0, "ch": 19},
    "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 20},
    "end": {"line": 0, "ch": 24},
    "class": {"className": "cm-f1_end"}}
])) {
  throw new Error("Test 5 failed!");
}

// 6) with 2 columns but starting with a non starting field
source = '<2>x</2><1>a</1><2>b</2><1>c</1>';
res = generateCSV(source, newf1);
if (res.csv != 'f1;f2;\na;b;\nc;;\n' || JSON.stringify(res.markers) != JSON.stringify([
  {"start": {"line": 0, "ch": 0},
   "end": {"line": 0, "ch": 3},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 4},
   "end": {"line": 0, "ch": 8},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 8},
   "end": {"line": 0, "ch": 11},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 12},
   "end": {"line": 0, "ch": 16},
   "class": {"className": "cm-f1_end"}},
  {"start": {"line": 0, "ch": 16},
   "end": {"line": 0, "ch": 19},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 20},
   "end": {"line": 0, "ch": 24},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 24},
   "end": {"line": 0, "ch": 27},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 28},
   "end": {"line": 0, "ch": 32},
   "class": {"className": "cm-f1_end"}}
])) {
  throw new Error("Test 6 failed!");
}

// 7) with 2 columns, one repeating 2 times
source = '<1>a</1><2>b</2><2>b</2><1>c</1>';
res = generateCSV(source, newf1);
if (res.csv != 'f1;f2;\na;b, b;\nc;;\n' || JSON.stringify(res.markers) != JSON.stringify([
  {"start": {"line": 0, "ch": 0},
   "end": {"line": 0, "ch": 3},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 4},
   "end": {"line": 0, "ch": 8},
   "class": {"className": "cm-f1_end"}},
  {"start": {"line": 0, "ch": 8},
   "end": {"line": 0, "ch": 11},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 12},
   "end": {"line": 0, "ch": 16},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 16},
   "end": {"line": 0, "ch": 19},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 20},
   "end": {"line": 0, "ch": 24},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 24},
   "end": {"line": 0, "ch": 27},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 28},
   "end": {"line": 0, "ch": 32},
   "class": {"className": "cm-f1_end"}}
])) {
  throw new Error("Test 7 failed!");
}

// 8) with 2 columns, one repeating 4 times
source = '<1>a</1><2>b</2><2>b</2><2>b</2><2>b</2><1>c</1>';
res = generateCSV(source, newf1);
if (res.csv != 'f1;f2;\na;b, b, b, b;\nc;;\n' || JSON.stringify(res.markers) != JSON.stringify([
  {"start": {"line": 0, "ch": 0},
   "end": {"line": 0, "ch": 3},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 4},
   "end": {"line": 0, "ch": 8},
   "class": {"className": "cm-f1_end"}},
  {"start": {"line": 0, "ch": 8},
   "end": {"line": 0, "ch": 11},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 12},
   "end": {"line": 0, "ch": 16},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 16},
   "end": {"line": 0, "ch": 19},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 20},
   "end": {"line": 0, "ch": 24},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 24},
   "end": {"line": 0, "ch": 27},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 28},
   "end": {"line": 0, "ch": 32},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 32},
   "end": {"line": 0, "ch": 35},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 36},
   "end": {"line": 0, "ch": 40},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 40},
   "end": {"line": 0, "ch": 43},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 44},
   "end": {"line": 0, "ch": 48},
   "class": {"className": "cm-f1_end"}}
])) {
  throw new Error("Test 8 failed!");
}

// 9) with 3 columns, one repeating 2 times only even if it repeats after the third field
source = '<1>a</1><2>b</2><2>b</2><3>c</3><2>d</2><1>e</1>';
newf1 = [
  ...newf1,
  {
    fieldName: 'f3',
    start: '<3>',
    startCol: f2StartCol[0],
    end: '</3>',
    endCol: f2EndCol[0]
  }
];
res = generateCSV(source, newf1);
if (res.csv != 'f1;f2;f3;\na;b, b;c;\ne;;;\n' || JSON.stringify(res.markers) != JSON.stringify([
  {"start": {"line": 0, "ch": 0},
   "end": {"line": 0, "ch": 3},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 4},
   "end": {"line": 0, "ch": 8},
   "class": {"className": "cm-f1_end"}},
  {"start": {"line": 0, "ch": 8},
   "end": {"line": 0, "ch": 11},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 12},
   "end": {"line": 0, "ch": 16},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 16},
   "end": {"line": 0, "ch": 19},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 20},
   "end": {"line": 0, "ch": 24},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 24},
   "end": {"line": 0, "ch": 27},
   "class": {"className": "cm-f3_start"}},
  {"start": {"line": 0, "ch": 28},
   "end": {"line": 0, "ch": 32},
   "class": {"className": "cm-f3_end"}},
  {"start": {"line": 0, "ch": 40},
   "end": {"line": 0, "ch": 43},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 44},
   "end": {"line": 0, "ch": 48},
   "class": {"className": "cm-f1_end"}}
])) {
  throw new Error("Test 9 failed!");
}

// 10) with 3 columns, one repeating 2 times only even if it repeats 2 times after the third field
source = '<1>a</1><2>b</2><2>b</2><3>c</3><2>d</2><2>d</2><1>e</1>';
res = generateCSV(source, newf1);
if (res.csv != 'f1;f2;f3;\na;b, b;c;\ne;;;\n' || JSON.stringify(res.markers) != JSON.stringify([
  {"start": {"line": 0, "ch": 0},
   "end": {"line": 0, "ch": 3},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 4},
   "end": {"line": 0, "ch": 8},
   "class": {"className": "cm-f1_end"}},
  {"start": {"line": 0, "ch": 8},
   "end": {"line": 0, "ch": 11},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 12},
   "end": {"line": 0, "ch": 16},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 16},
   "end": {"line": 0, "ch": 19},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 20},
   "end": {"line": 0, "ch": 24},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 24},
   "end": {"line": 0, "ch": 27},
   "class": {"className": "cm-f3_start"}},
  {"start": {"line": 0, "ch": 28},
   "end": {"line": 0, "ch": 32},
   "class": {"className": "cm-f3_end"}},
  {"start": {"line": 0, "ch": 48},
   "end": {"line": 0, "ch": 51},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 52},
   "end": {"line": 0, "ch": 56},
   "class": {"className": "cm-f1_end"}}
])) {
  throw new Error("Test 10 failed!");
}

// 11) with 3 columns, the 2nd one repeating 2 times and preceded by the 3th column
source = '<1>a</1><3>c</3><2>b</2><2>b</2><3>c</3><1>e</1>';
res = generateCSV(source, newf1);
if (res.csv != 'f1;f2;f3;\na;b, b;c;\ne;;;\n' || JSON.stringify(res.markers) != JSON.stringify([
  {"start": {"line": 0, "ch": 0},
   "end": {"line": 0, "ch": 3},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 4},
   "end": {"line": 0, "ch": 8},
   "class": {"className": "cm-f1_end"}},
  {"start": {"line": 0, "ch": 16},
   "end": {"line": 0, "ch": 19},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 20},
   "end": {"line": 0, "ch": 24},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 24},
   "end": {"line": 0, "ch": 27},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 28},
   "end": {"line": 0, "ch": 32},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 32},
   "end": {"line": 0, "ch": 35},
   "class": {"className": "cm-f3_start"}},
  {"start": {"line": 0, "ch": 36},
   "end": {"line": 0, "ch": 40},
   "class": {"className": "cm-f3_end"}},
  {"start": {"line": 0, "ch": 40},
   "end": {"line": 0, "ch": 43},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 44},
   "end": {"line": 0, "ch": 48},
   "class": {"className": "cm-f1_end"}}
])) {
  throw new Error("Test 11 failed!");
}

// 12) with 3 columns, the 3rd one repeating 2 times but preceded by the 2th column
source = '<1>a</1><2>b</2><3>c</3><2>x</2><3>c</3><1>e</1>';
res = generateCSV(source, newf1);
// could also or should be 'f1;f2;f3;\na;b;c;\ne;;;\n'
if (res.csv != 'f1;f2;f3;\na;b;c, c;\ne;;;\n' || JSON.stringify(res.markers) != JSON.stringify([
  {"start": {"line": 0, "ch": 0},
   "end": {"line": 0, "ch": 3},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 4},
   "end": {"line": 0, "ch": 8},
   "class": {"className": "cm-f1_end"}},
  {"start": {"line": 0, "ch": 8},
   "end": {"line": 0, "ch": 11},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 12},
   "end": {"line": 0, "ch": 16},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 16},
   "end": {"line": 0, "ch": 19},
   "class": {"className": "cm-f3_start"}},
  {"start": {"line": 0, "ch": 20},
   "end": {"line": 0, "ch": 24},
   "class": {"className": "cm-f3_end"}},
  {"start": {"line": 0, "ch": 32},
   "end": {"line": 0, "ch": 35},
   "class": {"className": "cm-f3_start"}},
  {"start": {"line": 0, "ch": 36},
   "end": {"line": 0, "ch": 40},
   "class": {"className": "cm-f3_end"}},
  {"start": {"line": 0, "ch": 40},
   "end": {"line": 0, "ch": 43},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 44},
   "end": {"line": 0, "ch": 48},
   "class": {"className": "cm-f1_end"}}
])) {
  throw new Error("Test 12 failed!");
}

// 13) with 3 columns, the 3rd one repeating 2 times and disrupted by the 2th column
source = '<1>a</1><3>c</3><1>e</1><2>b</2>';
res = generateCSV(source, newf1);
if (res.csv != 'f1;f2;f3;\na;;c;\ne;b;;\n' || JSON.stringify(res.markers) != JSON.stringify([
  {"start": {"line": 0, "ch": 0},
   "end": {"line": 0, "ch": 3},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 4},
   "end": {"line": 0, "ch": 8},
   "class": {"className": "cm-f1_end"}},
  {"start": {"line": 0, "ch": 8},
   "end": {"line": 0, "ch": 11},
   "class": {"className": "cm-f3_start"}},
  {"start": {"line": 0, "ch": 12},
   "end": {"line": 0, "ch": 16},
   "class": {"className": "cm-f3_end"}},
  {"start": {"line": 0, "ch": 16},
   "end": {"line": 0, "ch": 19},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 20},
   "end": {"line": 0, "ch": 24},
   "class": {"className": "cm-f1_end"}},
  {"start": {"line": 0, "ch": 24},
   "end": {"line": 0, "ch": 27},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 28},
   "end": {"line": 0, "ch": 32},
   "class": {"className": "cm-f2_end"}}
])) {
  throw new Error("Test 13 failed!");
}

// 14) with 3 columns, the 3rd one repeating 2 times and disrupted by the 2th column
source = '<2>b</2><1>a</1><3>c</3><1>e</1>';
res = generateCSV(source, newf1); // 'f1;f2;f3;\na;b;c;\ne;;;\n'
if (res.csv != 'f1;f2;f3;\na;;c;\ne;;;\n' || JSON.stringify(res.markers) != JSON.stringify([
  {"start": {"line": 0, "ch": 0},
   "end": {"line": 0, "ch": 3},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 4},
   "end": {"line": 0, "ch": 8},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 8},
   "end": {"line": 0, "ch": 11},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 12},
   "end": {"line": 0, "ch": 16},
   "class": {"className": "cm-f1_end"}},
  {"start": {"line": 0, "ch": 16},
   "end": {"line": 0, "ch": 19},
   "class": {"className": "cm-f3_start"}},
  {"start": {"line": 0, "ch": 20},
   "end": {"line": 0, "ch": 24},
   "class": {"className": "cm-f3_end"}},
  {"start": {"line": 0, "ch": 24},
   "end": {"line": 0, "ch": 27},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 28},
   "end": {"line": 0, "ch": 32},
   "class": {"className": "cm-f1_end"}}
])) {
  throw new Error("Test 14 failed!");
}

// 15) try a single char as delimiter
source = '$$AA#BB$$';
var fieldSet = [
  {
    fieldName: 'f1',
    start: '\\$\\$',
    startCol: ['rgb(255, 0, 0)'],
    end: '(?=#)',
    endCol: ['rgb(0, 0, 255)']
  },
  {
    fieldName: 'f2',
    start: '#',
    startCol: ['rgb(255, 0, 0)'],
    end: '\\$\\$',
    endCol: ['rgb(0, 0, 255)']
  }
];
res = generateCSV(source, fieldSet); // 'f1;f2;\na;b;\n'
if (res.csv != 'f1;f2;\nAA;BB;\n' || JSON.stringify(res.markers) != JSON.stringify([
  {"start": {"line": 0, "ch": 0},
   "end": {"line": 0, "ch": 2},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 4},
   "end": {"line": 0, "ch": 4},
   "class": {"className": "cm-f1_end"}},
  {"start": {"line": 0, "ch": 4},
   "end": {"line": 0, "ch": 5},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 7},
   "end": {"line": 0, "ch": 9},
   "class": {"className": "cm-f2_end"}}
])) {
  throw new Error("Test 15 failed!");
}

// 16) try a single char as delimiter
source = 'xaaaaabyc';
var fieldSet = [
  {
    fieldName: 'f1',
    start: 'b',
    startCol: ['rgb(255, 0, 0)'],
    end: 'c',
    endCol: ['rgb(0, 0, 255)']
  },
  {
    fieldName: 'f2',
    start: 'x',
    startCol: ['rgb(255, 0, 0)'],
    end: 'y',
    endCol: ['rgb(0, 0, 255)']
  }
];
res = generateCSV(source, fieldSet);
if (res.csv != 'f1;f2;\ny;;\n' || JSON.stringify(res.markers) != JSON.stringify([
  {"start": {"line": 0, "ch": 0},
   "end": {"line": 0, "ch": 1},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 7},
   "end": {"line": 0, "ch": 8},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 6},
   "end": {"line": 0, "ch": 7},
   "class": {"className": "cm-f1_start"}},
  {"start": {"line": 0, "ch": 8},
   "end": {"line": 0, "ch": 9},
   "class": {"className": "cm-f1_end"}}
])) {
  throw new Error("Test 16 failed!");
}

// 17) try a specific case
source = 'abab';
var fieldSet = [
  {
    fieldName: 'f1',
    start: 'c',
    startCol: ['rgb(255, 0, 0)'],
    end: 'd',
    endCol: ['rgb(0, 0, 255)']
  },
  {
    fieldName: 'f2',
    start: 'a',
    startCol: ['rgb(255, 0, 0)'],
    end: 'b',
    endCol: ['rgb(0, 0, 255)']
  }
];
res = generateCSV(source, fieldSet);
if (res.csv != 'f1;f2;\n' || JSON.stringify(res.markers) != JSON.stringify([
  {"start": {"line": 0, "ch": 0},
   "end": {"line": 0, "ch": 1},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 1},
   "end": {"line": 0, "ch": 2},
   "class": {"className": "cm-f2_end"}},
  {"start": {"line": 0, "ch": 2},
   "end": {"line": 0, "ch": 3},
   "class": {"className": "cm-f2_start"}},
  {"start": {"line": 0, "ch": 3},
   "end": {"line": 0, "ch": 4},
   "class": {"className": "cm-f2_end"}}
])) {
  throw new Error("Test 17 failed!");
}



// basic tests
var x = parseCSV('a,bx,c,\n1,2,3,');
x = parseCSV('a\nb');
x = parseCSV('a\nb\n');
x = parseCSV('a\nb\n\n');

x = parseCSV('a,\nb,');
x = parseCSV('a,\nb,\n');
x = parseCSV('a,\nb,\n\n');

// nothing
x = parseCSV();
x = parseCSV('');

// quote alone (no quoted string)
x = parseCSV('a,b,c,\n1,2",3,');

// quote alone before end of line (\n)
x = parseCSV('a,b"\n1,2');

// quote alone before end of line (\r)
x = parseCSV('a,b"\r1,2');

// quote alone at the end of file
x = parseCSV('a,b,\n1,2"');

// quoted string
x = parseCSV('a,b,c,\n1,"2",3,');

// true quoted string before \n
x = parseCSV('a,"b"\n1,2');

// true quoted string having a \n
x = parseCSV('a,"b\nc"\n1,2');

// true quoted string having an escaped quote
x = parseCSV('a,"b""c"\n1,2');

// true quoted string having an escaped quote
x = parseCSV('a,"b"""\n1,2');

// true quoted string having an escaped quote
x = parseCSV('a,"b"","\n1,2');

// true quoted string having an escaped quote
x = parseCSV('a,"b"","\n1,2');

// true quoted string having an escaped quote
x = parseCSV('a,""","\n1,2');
