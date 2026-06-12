				<div class="relative flex items-center">
					<button type="button" class="p-2 flex-shrink-0 transition-colors duration-300" style="color:var(--color-text-secondary)"
							onclick="var w=document.getElementById('hs-wrap');var i=document.getElementById('hs-input');
								if(w.style.maxWidth==='200px'){w.style.maxWidth='0';w.style.opacity='0';setTimeout(()=>i.value='',300)}
								else{w.style.maxWidth='200px';w.style.opacity='1';setTimeout(()=>i.focus(),100)}"
							aria-label="Поиск"
							onmouseover="this.style.color='var(--color-text)'"
							onmouseout="this.style.color='var(--color-text-secondary)'"
					>
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
						</svg>
					</button>
					<div class="absolute right-0 top-1/2 -translate-y-1/2 overflow-hidden transition-all duration-300 ease-in-out"
						id="hs-wrap"
						style="max-width:0;opacity:0;">
						<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="pl-2">
							<input type="search" name="s" id="hs-input"
									class="w-[200px] py-1.5 px-3 text-sm rounded-full transition-colors duration-300 focus:outline-none"
									style="
										background: var(--color-surface);
										color: var(--color-text);
										border: 1.5px solid var(--color-accent);
									"
									placeholder="Поиск ..."
									value="<?php echo get_search_query(); ?>"
							/>
						</form>
					</div>
				</div>