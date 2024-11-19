export function loadScript(src) { // Function to dynamically load scripts
    const script = document.createElement('script');
    script.src = src;
    script.type = 'module';
    document.body.appendChild(script);
}