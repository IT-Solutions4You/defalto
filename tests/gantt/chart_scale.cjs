// Exercise the real adapter without a browser, CRM bootstrap or database writes.
const fs = require('node:fs'),
    vm = require('node:vm'),
    assert = require('node:assert/strict'),
    path = require('node:path');
let adapter;
vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../layouts/d1/modules/Project/resources/Chart.js'), 'utf8'), {
    Date, Vtiger: {Class(name, staticMembers, methods) {
        assert.equal(name, 'Project_Chart_Js');
        adapter = methods;
    }}
});

for (const zone of ['UTC', 'Europe/Bratislava', 'America/New_York', 'Pacific/Kiritimati']) {
    process.env.TZ = zone;

    for (const [start, end, expected] of [
        ['2026-03-15', '2026-03-15', 0],
        ['2026-03-15', '2026-04-13', 0], // 30 inclusive days across spring DST
        ['2026-03-15', '2026-04-14', 1], // 31
        ['2026-03-15', '2026-05-09', 1], // 56
        ['2026-03-15', '2026-05-10', 2], // 57
        ['2026-10-10', '2026-11-08', 0], // 30 across autumn DST
        ['2026-10-10', '2026-11-09', 1],
        ['2026-10-10', '2026-12-04', 1],
        ['2026-10-10', '2026-12-05', 2],
        ['2028-02-01', '2028-03-01', 0], // leap year
        ['', '2026-11-01', 2],
        ['2026-11-01', '', 2],
        ['2026-11-02', '2026-11-01', 2]
    ]) {
        assert.equal(adapter.getInitialScale(start, end), expected, `${zone}: ${start}–${end}`);
    }
}

console.log('Initial chart scales verified at 1/30/31/56/57 days in four time zones, DST and leap year included.');

adapter.chart = {config: {}};

for (const [end, units] of [['2026-04-13', 'month/day'], ['2026-05-09', 'month/week'], ['2026-05-10', 'year/month']]) {
    adapter.setScale(adapter.getInitialScale('2026-03-15', end), false);
    assert.equal(adapter.chart.config.scales.map(row => row.unit).join('/'), units);
}

console.log('Actual scale configuration verified for daily, weekly and monthly views.');
