const prev = document.getElementById('prev');
const next = document.getElementById('next');
const slider = document.querySelector('.slider');
let step;

next.addEventListener('click' , () => {
  step = 1 ;
  slider.style.transform = 'translateX(-304px)'
})

prev.addEventListener('click' , async () => {
  step = -1 ;
  slider.style.transition = await 'none';
  slider.prepend(slider.lastElementChild);
  slider.style.transform = 'translateX(-304px)'
  setTimeout(async () => {
    slider.style.transition = await '.3s ease-in-out';
    slider.style.transform = 'translateX(0)'
  })
})

slider.addEventListener('transitionend' , async () => {
  if (step === 1) {
    
    slider.style.transition = await 'none';
    slider.append(slider.firstElementChild);
    slider.style.transform  = await 'translateX(0)';
    setTimeout(() => {
      slider.style.transition = '.3s ease-in-out';
    })
  }
})