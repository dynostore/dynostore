from typing import Optional
import typer
from dynoadmin import __app_name__, __version__, ERRORS, user

app = typer.Typer()

def _version_callback(value: bool) -> None:
    if value:
        typer.echo(f"{__app_name__} v{__version__}")
        raise typer.Exit()

@app.command()
def create_user(
    username: str = typer.Option(..., prompt=True, help="Username"),
    password: str = typer.Option(..., prompt=True, hide_input=True, help="Password"),
    email: str = typer.Option(..., prompt=True, help="Email"),
) -> None:
    """Create user"""
    try:
        results = user.create_user(username, password, email)
        typer.secho(
            f'User created with token "{results["data"]["access_token"]}"',
            fg=typer.colors.GREEN,
        )
        
        typer.secho(
            f'APIKEY "{results["data"]["apikey"]}"',
            fg=typer.colors.GREEN,
        )
        
        typer.secho(
            f'Please, store the apikey and token in a safe place. It will not be shown again.',
            fg=typer.colors.BLUE,
        )
        
        
    except Exception as e:
        typer.secho(
            f'Creating user failed with error: {e}',
            fg=typer.colors.RED,
        )
        raise typer.Exit(1)

@app.callback()
def main(
    version: Optional[bool] = typer.Option(
        None,
        "--version",
        "-v",
        help="Show the application's version and exit.",
        callback=_version_callback,
        is_eager=True,
    )
) -> None:
    return