// Copyright Up and Running

function FillCanvases() {

    // dots which are uniformed
    c[2].radius = 20;
    c[2].space = 110;
    c[2].context = c[2].canvas[0].getContext('2d');
    c[2].dots = new Array([c[2].radius * 2, c[2].radius * 2, c[2].radius],
        [c[2].radius * 2 + c[2].space, c[2].radius * 2, c[2].radius],
        [c[2].radius * 2 + c[2].space * 2, c[2].radius * 2, c[2].radius],
        [c[2].radius * 2, c[2].radius * 2 + c[2].space, c[2].radius],
        [c[2].radius * 2 + c[2].space, c[2].radius * 2 + c[2].space, c[2].radius],
        [c[2].radius * 2 + c[2].space * 2, c[2].radius * 2 + c[2].space, c[2].radius]);
    DrawShape(c[2].context, c[2].dots);

    // defined shape of dots
    c[3].context = c[3].canvas[0].getContext('2d');
    c[3].dots = new Array([21, 121, 21],
        [201, 11, 10],
        [303, 177, 14],
        [256, 375, 8],
        [174, 368, 20],
        [108, 362, 8],
        [120, 217, 12],
        [208, 234, 10],
        [17, 354, 15]);
    c[3].conn = new Array([0, 1],
        [1, 2],
        [2, 3],
        [3, 4],
        [4, 5],
        [5, 6],
        [6, 7],
        [7, 4],
        [5, 8],
        [8, 0],
        [0, 2]);
    DrawShape(c[3].context, c[3].dots);
}

// draw a constellation from defined dots/stars
function DrawShape(__context, __shape) {
    for (var s = 0; s < __shape.length; s++) {
        DrawCircle(__context, __shape[s][0], __shape[s][1], __shape[s][2]);
    }
}

// draw an individual circle/star
function DrawCircle(__context, __x, __y, __radius) {
    __context.beginPath();
    __context.arc(__x, __y, __radius, 0, 2 * Math.PI, false);
    __context.fillStyle = DOT_COLOR;
    __context.fill();
}

// will connect all the stars with lines, lines which are gradually drown
// this will try to simulate like you connect all of the stars one by one
function ConnectShape(__canvas, __multiplier) {

    var count = 0,
        length = __canvas.conn.length,
        shape_interval,
        multiplier = 1;

    if (__multiplier != null) {
        multiplier = __multiplier;
    }
    shape_interval = setInterval(function () {
        DrawLine(__canvas.context, __canvas.dots[__canvas.conn[count][0]], __canvas.dots[__canvas.conn[count][1]]);
        count++;
        if (count === length) {
            clearInterval(shape_interval);
        }
    }, 200 / multiplier);
}

// gradually connect two dots/stars
function DrawLine(__context, __start, __end) {

    var line_interval,
        amount = 0;

    line_interval = setInterval(function () {
        __context.globalCompositeOperation = 'destination-over';
        __context.beginPath();
        __context.moveTo(__start[0] + (__end[0] - __start[0]) * amount, __start[1] + (__end[1] - __start[1]) * amount);
        amount += 0.1;
        if (amount > 1) {
            amount = 1;
            clearInterval(line_interval);
        }
        __context.lineTo(__start[0] + (__end[0] - __start[0]) * amount, __start[1] + (__end[1] - __start[1]) * amount);
        __context.strokeStyle = LINE_COLOR;
        __context.stroke();
    }, 30);
}
