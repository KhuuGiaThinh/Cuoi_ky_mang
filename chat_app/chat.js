function initChat(userId){
    let selectedUser = null;
    const messagesDiv = document.getElementById("messages");
    const ws = new WebSocket(`ws://localhost:8080/?user_id=${userId}`);

    ws.onopen = () => console.log("WebSocket connected");
    ws.onclose = () => console.log("WebSocket disconnected");
    ws.onerror = e => console.error("WebSocket error:", e);

    ws.onmessage = e => {
        let data;
        try { data = JSON.parse(e.data); } 
        catch(err){ console.error("Invalid JSON:", e.data); return; }

        if(!selectedUser) return;
        if(data.from_id == selectedUser || data.from_id == userId){
            const div = document.createElement("div");
            div.className = (data.from_id==userId) ? "message me" : "message them";
            div.textContent = data.message;
            messagesDiv.appendChild(div);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
    };

    function addMessage(type,text){
        const div = document.createElement("div");
        div.className = "message "+type;
        div.textContent = text;
        messagesDiv.appendChild(div);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function loadHistory(to_id){
        fetch(`chat.php?action=history&to_id=${to_id}`)
        .then(res => res.json())
        .then(data=>{
            messagesDiv.innerHTML = "";
            data.forEach(msg => addMessage(msg.id_nguoi_gui==userId?"me":"them", msg.noi_dung));
        }).catch(err=>{
            console.error("Load history failed:", err);
        });
    }

    document.querySelectorAll(".user-item").forEach(el=>{
        el.addEventListener("click",()=>{
            document.querySelectorAll(".user-item").forEach(x=>x.classList.remove("active"));
            el.classList.add("active");
            selectedUser = el.dataset.id;
            loadHistory(selectedUser);
        });
    });

    document.getElementById("sendBtn").addEventListener("click",()=>{
        const msg = document.getElementById("msgInput").value.trim();
        if(msg && selectedUser && ws.readyState===WebSocket.OPEN){
            ws.send(JSON.stringify({to_id:selectedUser,message:msg}));
            document.getElementById("msgInput").value="";
        }
    });

    document.getElementById("msgInput").addEventListener("keypress", e=>{
        if(e.key==="Enter") document.getElementById("sendBtn").click();
    });
}
