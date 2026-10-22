(() => {
  const form=document.getElementById('reserve-form');if(!form)return;
  const selected=new Map();const buttons=[...document.querySelectorAll('button.seat:not(:disabled)')];
  const formatter=new Intl.NumberFormat('vi-VN');
  const fmt=n=>formatter.format(n)+'đ';
  const quantities=[...document.querySelectorAll('.product-qty')];
  const seatSum=document.getElementById('seat-total');const foodSum=document.getElementById('food-total');const grand=document.getElementById('grand-total');
  const label=document.getElementById('selected-list');const inputWrap=document.getElementById('selected-seats');const submit=document.getElementById('submit-booking');
  function update(){
    const entries=[...selected.values()]; const s=entries.reduce((a,x)=>a+x.price,0);
    const f=quantities.reduce((a,x)=>a+Number(x.value||0)*Number(x.dataset.price||0),0);
    seatSum.textContent=fmt(s);foodSum.textContent=fmt(f);grand.textContent=fmt(s+f);
    label.textContent=entries.length?`Ghế: ${entries.map(e=>e.code).join(', ')}`:'Chưa chọn ghế';
    inputWrap.replaceChildren();for(const seat of entries){const i=document.createElement('input');i.type='hidden';i.name='seats[]';i.value=seat.id;inputWrap.append(i)}
    submit.disabled=entries.length===0;
  }
  for(const b of buttons)b.addEventListener('click',()=>{const id=Number(b.dataset.id);if(selected.has(id)){selected.delete(id);b.classList.remove('is-selected')}else{if(selected.size>=8){alert('Mỗi đơn chọn tối đa 8 ghế.');return}selected.set(id,{id,price:Number(b.dataset.price),code:b.dataset.code});b.classList.add('is-selected')}update()});
  quantities.forEach(x=>x.addEventListener('input',update));update();
})();
