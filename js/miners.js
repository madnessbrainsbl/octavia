document.addEventListener("DOMContentLoaded", function () {
  const btn = document.getElementById("refreshMiners");
  const tbody = document.querySelector("#minersTable tbody");

  // Function to validate IP range format
  function validateIpRange(range) {
    if (!range) return false;

    // Single IP address
    const singleIpRegex = /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/;
    if (singleIpRegex.test(range)) {
      const parts = range.split(".");
      return parts.every((part) => {
        const num = parseInt(part);
        return num >= 0 && num <= 255;
      });
    }

    // IP range (e.g., 192.168.1.1-254 or 192.168.1.1-192.168.1.254)
    const rangeRegex =
      /^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})-(\d{1,3}(\.\d{1,3}\.\d{1,3}\.\d{1,3})?)$/;
    const match = range.match(rangeRegex);
    if (match) {
      const startIp = match[1];
      let endIp;

      if (match[3]) {
        // Full IP range
        endIp = match[2];
      } else {
        // Short range (just last octet)
        const startParts = startIp.split(".");
        startParts[3] = match[2];
        endIp = startParts.join(".");
      }

      // Validate both IPs
      const startParts = startIp.split(".");
      const endParts = endIp.split(".");

      const startValid = startParts.every((part) => {
        const num = parseInt(part);
        return num >= 0 && num <= 255;
      });

      const endValid = endParts.every((part) => {
        const num = parseInt(part);
        return num >= 0 && num <= 255;
      });

      return startValid && endValid;
    }

    // Comma-separated IPs
    if (range.includes(",")) {
      const ips = range.split(",").map((ip) => ip.trim());
      return ips.every((ip) => singleIpRegex.test(ip) && validateIpRange(ip));
    }

    return false;
  }

  async function loadMiners() {
    const range = document.getElementById("scanRange").value;

    // Validate IP range format
    if (!validateIpRange(range)) {
      alert(
        "Неверный формат IP диапазона. Используйте формат: 192.168.1.1-254 или 192.168.1.1-192.168.1.254"
      );
      return;
    }

    btn.disabled = true;
    btn.textContent = "Сканирование...";
    tbody.innerHTML = '<tr><td colspan="11">Загрузка...</td></tr>';
    try {
      const response = await fetch(
        `api/miner_control.php?action=scan&ip_range=${encodeURIComponent(
          range
        )}&direct_scan=1`
      );
      if (!response.ok) throw new Error("Network response was not ok");
      const miners = await response.json();
      tbody.innerHTML = "";
      if (!miners || miners.length === 0) {
        tbody.innerHTML = '<tr><td colspan="11">Нет майнеров в сети.</td></tr>';
      } else {
        miners.forEach((miner) => {
          const tr = document.createElement("tr");

          // Format status with color
          let statusClass = "";
          if (miner.status === "Mining") statusClass = "text-success";
          else if (miner.status === "Error") statusClass = "text-danger";
          else if (miner.status === "Offline") statusClass = "text-muted";

          // Format pool and worker
          let poolInfo = miner.pool;
          if (miner.worker) {
            poolInfo += `<br><small class="text-muted">${miner.worker}</small>`;
          }

          // Additional info
          let additionalInfo = "";
          if (miner.power !== "N/A")
            additionalInfo += `Power: ${miner.power}<br>`;
          if (miner.uptime !== "N/A")
            additionalInfo += `Uptime: ${miner.uptime}<br>`;
          if (miner.fans !== "N/A") additionalInfo += `Fans: ${miner.fans}<br>`;
          if (miner.coolingMode !== "N/A")
            additionalInfo += `Cooling: ${miner.coolingMode}<br>`;
          if (miner.devFee !== "N/A")
            additionalInfo += `Dev Fee: ${miner.devFee}`;

          tr.innerHTML = `
                        <td><input type="checkbox" class="selectMiner" value="${miner.ip}"></td>
                        <td class="model">${miner.model}</td>
                        <td class="ip">${miner.ip}<br><small class="text-muted">${miner.mac}</small></td>
                        <td class="platform">${miner.platform}<br><small class="text-muted">${miner.firmware}</small></td>
                        <td class="status ${statusClass}">${miner.status}</td>
                        <td class="hashrate">${miner.hashrate}</td>
                        <td class="temperature">${miner.temperature}</td>
                        <td class="pool">${poolInfo}</td>
                        <td class="additional-info"><small>${additionalInfo}</small></td>
                        <td>
                            <button class="btn btn-sm btn-warning restartMiner" data-ip="${miner.ip}">Перезапустить</button>
                            <button class="btn btn-sm btn-danger stopMiner" data-ip="${miner.ip}">Стоп</button>
                        </td>
                    `;
          tbody.appendChild(tr);

          // Attach control handlers
          tr.querySelector(".restartMiner").addEventListener("click", () =>
            controlMiner(miner.ip, "restart", tr)
          );
          tr.querySelector(".stopMiner").addEventListener("click", () =>
            controlMiner(miner.ip, "pause", tr)
          );
        });
      }
    } catch (error) {
      console.error("Ошибка при получении майнеров:", error);
      tbody.innerHTML =
        '<tr><td colspan="11" class="text-danger">Ошибка при получении списка майнеров.</td></tr>';
    }
    btn.disabled = false;
    btn.textContent = "Сканировать";
  }

  async function controlMiner(ip, action, tr) {
    const btns = tr.querySelectorAll("button");
    btns.forEach((b) => (b.disabled = true));
    try {
      const res = await fetch(
        `api/miner_control.php?action=${action}&ip_range=${encodeURIComponent(
          ip
        )}`
      );
      const json = await res.json();
      console.log("Control", action, json);
      // Reload details after action
      const statusCell = tr.querySelector(".status");
      statusCell.textContent = "Обновление...";
    } catch (e) {
      console.error("Control error", e);
    } finally {
      btns.forEach((b) => (b.disabled = false));
    }
  }

  btn.addEventListener("click", loadMiners);
  loadMiners();

  function getSelectedMiners() {
    const selected = [];
    document.querySelectorAll(".selectMiner:checked").forEach((cb) => {
      selected.push(cb.value);
    });
    return selected;
  }

  function updateButtonStates() {
    const selected = getSelectedMiners();
    const hasSelection = selected.length > 0;
    document.getElementById("massRestart").disabled = !hasSelection;
    document.getElementById("massReboot").disabled = !hasSelection;
    document.getElementById("massPause").disabled = !hasSelection;
    document.getElementById("massStart").disabled = !hasSelection;
    document.getElementById("massPoolSwitch").disabled = !hasSelection;
    document.getElementById("massNetworkConfig").disabled = !hasSelection;
    document.getElementById("updateFirmware").disabled = !hasSelection;
    document.getElementById("removeFirmware").disabled = !hasSelection;
  }

  document.getElementById("selectAll").addEventListener("click", () => {
    document
      .querySelectorAll(".selectMiner")
      .forEach((cb) => (cb.checked = true));
    updateButtonStates();
  });

  document.getElementById("deselectAll").addEventListener("click", () => {
    document
      .querySelectorAll(".selectMiner")
      .forEach((cb) => (cb.checked = false));
    updateButtonStates();
  });

  document
    .getElementById("selectAllCheckbox")
    .addEventListener("change", (e) => {
      document
        .querySelectorAll(".selectMiner")
        .forEach((cb) => (cb.checked = e.target.checked));
      updateButtonStates();
    });

  tbody.addEventListener("change", (e) => {
    if (e.target.classList.contains("selectMiner")) {
      updateButtonStates();
    }
  });

  async function massOperation(action) {
    const selected = getSelectedMiners();
    if (selected.length === 0) return;

    const range = selected.join(",");
    try {
      const res = await fetch(
        `api/miner_control.php?action=${action}&ip_range=${encodeURIComponent(
          range
        )}`
      );
      const json = await res.json();
      console.log("Mass operation", action, json);
      alert(json.output || "Операция выполнена");
    } catch (e) {
      console.error("Mass operation error", e);
      alert("Ошибка при выполнении операции");
    }
  }

  document
    .getElementById("massRestart")
    .addEventListener("click", () => massOperation("restart"));
  document
    .getElementById("massReboot")
    .addEventListener("click", () => massOperation("reboot"));
  document
    .getElementById("massPause")
    .addEventListener("click", () => massOperation("pause"));
  document
    .getElementById("massStart")
    .addEventListener("click", () => massOperation("start"));

  document
    .getElementById("massPoolSwitch")
    .addEventListener("click", async () => {
      const poolId = prompt("Введите ID пула (0-2):", "0");
      if (poolId === null) return;

      const selected = getSelectedMiners();
      const range = selected.join(",");

      try {
        const res = await fetch(`api/miner_control.php`, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `action=switch_pool&ip_range=${encodeURIComponent(
            range
          )}&pool_id=${poolId}`,
        });
        const json = await res.json();
        alert(json.output || "Пул переключен");
      } catch (e) {
        alert("Ошибка при переключении пула");
      }
    });

  document
    .getElementById("updateFirmware")
    .addEventListener("click", async () => {
      const selected = getSelectedMiners();
      if (selected.length === 0) return;

      const platform = document.getElementById("firmwarePlatform").value;
      const model = document.getElementById("firmwareModel").value;
      const version = document.getElementById("firmwareVersion").value;
      const fileInput = document.getElementById("firmwareFile");

      if (!fileInput.files.length || !version) {
        alert("Выберите файл прошивки и укажите версию");
        return;
      }

      const statusDiv = document.getElementById("firmwareStatus");
      statusDiv.textContent = "Загрузка прошивки...";

      const fd = new FormData();
      fd.append("action", "firmware_update");
      fd.append("ip_range", selected.join(","));
      fd.append("platform", platform);
      fd.append("model", model);
      fd.append("version", version);
      fd.append("file", fileInput.files[0]);

      try {
        const resp = await fetch("api/miner_control.php", {
          method: "POST",
          body: fd,
        });
        const result = await resp.json();
        statusDiv.textContent = result.output || "Прошивка обновлена";
      } catch (e) {
        statusDiv.textContent = "Ошибка при обновлении прошивки";
      }
    });

  document
    .getElementById("removeFirmware")
    .addEventListener("click", async () => {
      if (
        !confirm(
          "Вы уверены? Это удалит прошивку и вернет к заводским настройкам."
        )
      )
        return;

      const selected = getSelectedMiners();
      const range = selected.join(",");

      try {
        const res = await fetch(
          `api/miner_control.php?action=remove_firmware&ip_range=${encodeURIComponent(
            range
          )}`
        );
        const json = await res.json();
        alert(json.output || "Прошивка удалена");
      } catch (e) {
        alert("Ошибка при удалении прошивки");
      }
    });
});
